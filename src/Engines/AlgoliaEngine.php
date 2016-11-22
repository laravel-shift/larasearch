<?php

namespace Gtk\Larasearch\Engines;

use Gtk\Larasearch\Builder;
use AlgoliaSearch\Client as Algolia;
use Illuminate\Database\Eloquent\Collection;

class AlgoliaEngine extends Engine
{
    /**
     * The Algolia client.
     *
     * @var \AlgoliaSearch\Client
     */
    protected $algolia;

    /**
     * Create a new engine instance.
     *
     * @param  \AlgoliaSearch\Client  $algolia
     * @return void
     */
    public function __construct(Algolia $algolia)
    {
        $this->algolia = $algolia;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @throws \AlgoliaSearch\AlgoliaException
     * @return void
     */
    public function update($models)
    {
        $index = $this->algolia->initIndex($models->first()->searchableAs());

        $index->addObjects(
            $models->map(function ($model) {
                $searchableArray = $model->toSearchableArray();

                if (empty($searchableArray)) {
                    return;
                }

                return array_merge(['objectID' => $model->getKey()], $searchableArray);
            })->filter()->values()->all()
        );
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $index = $this->algolia->initIndex($models->first()->searchableAs());

        $index->deleteObjects(
            $models->map(function ($model) {
                return $model->getKey();
            })->values()->all()
        );
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Larasearch\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'hitsPerPage' => $builder->limit,
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Larasearch\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $results = $this->performSearch($builder, [
            'numericFilters' => $this->filters($builder),
            'hitsPerPage' => $perPage,
            'page' => $page - 1,
        ]);

        $builder->total = $results['nbHits'];

        return $results;
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Gtk\Larasearch\Builder  $builder
     * @param  array  $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $algolia = $this->algolia->initIndex(
            $builder->index ?: $builder->model->searchableAs()
        );

        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $algolia,
                $builder->query,
                $options
            );
        }

        return $algolia->search($builder->query, $options);
    }

    /**
     * Get the filter array for the query.
     *
     * @param  \Gtk\Larasearch\Builder  $builder
     * @return array
     */
    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->map(function ($value, $key) {
            return $key.'='.$value;
        })->values()->all();
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
        if (count($results['hits']) === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits'])->pluck('objectID')->values()->all();

        $models = $model->whereIn(
            $model->getQualifiedKeyName(), $keys
        )->get()->keyBy($model->getKeyName());

        return collect($results['hits'])->map(function ($hit) use ($model, $models) {
            $key = $hit[$model->getKeyName()];

            if (isset($models[$key])) {
                return $models[$key];
            }
        })->filter();
    }
}
