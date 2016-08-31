<?php

namespace Gtk\Scout\Engines;

use Closure;
use Gtk\Scout\Builder;
use Psr\Log\LoggerInterface;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client as Elastic;
use Illuminate\Database\Eloquent\Collection;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Connections\ConnectionFactoryInterface;

class ElasticEngine extends Engine
{
    /**
     * Create a new engine instance.
     *
     * @param  \Elasticsearch\Client $elastic
     * @return void
     */
    public function __construct(Elastic $elastic, $index)
    {
        $this->elastic = $elastic;
        $this->index = $index;
    }

    /**
     * Build a new client from the provided config.
     *
     * @param  array $config
     * @return \Elasticsearch\Client
     */
    public static function buildClient(array $config)
    {
        $clientBuilder = ClientBuilder::create()
            ->setHosts($config['hosts'])
            ->setRetries($config['retries'])
            ->setHandler(call_user_func('\Elasticsearch\ClientBuilder::' . $config['handler'] . 'Handler'))
            ->setConnectionPool($config['connection_pool'])
            ->setSelector($config['selector'])
            ->setSerializer($config['serializer']);

        if ($config['ssl_verification']) {
            $clientBuilder->setSSLVerification($config['ssl_verification']);
        }

        if ($config['log']) {
            $logger = isset($config['logger']) && $config['logger'] instanceof LoggerInterface
                ? $config['logger']
                : ClientBuilder::defaultLogger($config['log_path'], $config['log_level']);

            $clientBuilder->setLogger($logger);
        }

        if (isset($config['connection_factory']) && $config['connection_factory'] instanceof ConnectionFactoryInterface) {
            $clientBuilder->setConnectionFactory($config['connection_factory']);
        }

        if (isset($config['endpoint']) && $config['endpoint'] instanceof Closure) {
            $clientBuilder->setEndpoint($config['endpoint']);
        }

        return $clientBuilder->build();
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        $models->map(function ($model) {
            $params = $this->getParams($model);

            if ($this->getDocument($params)) {
                return $this->elastic->update(array_merge($params, ['body' => ['doc' => $model->toSearchableArray()]]));
            }

            return $this->elastic->index(array_merge($params, ['body' => $model->toSearchableArray()]));
        });
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $models->map(function ($model) {
            return $this->elastic->delete($this->getParams($model));
        });
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, ['size' => $builder->limit]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $results = $this->performSearch($builder, [
            'size' => $perPage,
            'from' => ($page - 1) * $perPage,
        ]);

        $builder->total = $results['hits']['total'] ? : $results->count();

        return $results;
    }

    /**
     * Get params by model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    protected function getParams($model)
    {
        return [
            'index' => $this->index,
            'type' => $model->searchableAs(),
            'id' => $model->getKey(),
        ];
    }

    /**
     * Get document by given params.
     *
     * @param  array $params
     * @return mixed
     */
    protected function getDocument($params)
    {
        try {
            return $this->elastic->get($params);
        } catch (Missing404Exception $e) {
            return false;
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Scout\Builder  $builder
     * @param  array  $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $builder->index ?: $builder->model->searchableAs(),
            'body' => $builder->query,
        ];

        if (isset($options['size'])) {
            $params['size'] = $options['size'];
        }

        if (isset($options['from'])) {
            $params['from'] = $options['from'];
        }

        return $this->elastic->search($params);
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map($results, $model)
    {
        if ($results['hits']['total'] === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        $models = $model->whereIn(
            $model->getKeyName(), $keys
        )->get()->keyBy($model->getKeyName());

        return collect($results['hits']['hits'])->map(function ($hit) use ($model, $models) {
            $key = $hit['_source'][$model->getKeyName()];

            return isset($models[$key]) ? $models[$key] : null;
        })->filter();
    }
}
