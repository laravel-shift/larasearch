<?php

namespace Tests;

use Mockery;
use Gtk\Larasearch\Builder;
use Gtk\Larasearch\Engines\ElasticEngine;
use Tests\Fixtures\ElasticEngineTestModel;
use Illuminate\Database\Eloquent\Collection;

class ElasticEngineTest extends AbstractTestCase
{
    public function test_update_adds_objects_to_index()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'refresh' => true,
            'body' => [
                [
                    'index' => [
                        '_index' => 'index_name',
                        '_type' => 'table',
                        '_id' => 1,
                    ],
                ],
                [
                    'id' => 1,
                ],
            ],
        ]);

        $engine = new ElasticEngine($client, 'index_name');
        $engine->update(Collection::make([new ElasticEngineTestModel]));
    }

    public function test_delete_removes_objects_to_index()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'refresh' => true,
            'body' => [
                [
                    'delete' => [
                        '_index' => 'index_name',
                        '_type' => 'table',
                        '_id' => 1,
                    ],
                ],
            ],
        ]);

        $engine = new ElasticEngine($client, 'index_name');
        $engine->delete(Collection::make([new ElasticEngineTestModel]));
    }

    public function test_search_sends_correct_parameters_to_elastic()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('search');

        $engine = new ElasticEngine($client, 'my_index');
        $builder = new Builder(new ElasticEngineTestModel, 'zonda');
        $builder->where('foo', 1);
        $engine->search($builder);
    }

    public function test_map_correctly_maps_results_to_models()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $engine = new ElasticEngine($client, 'my_index');

        $model = Mockery::mock('StdClass');
        $model->shouldReceive('getKeyName')->andReturn('id');
        $model->shouldReceive('getQualifiedKeyName')->andReturn('id');
        $model->shouldReceive('whereIn')->once()->with('id', [1])->andReturn($model);
        $model->shouldReceive('get')->once()->andReturn(Collection::make([new ElasticEngineTestModel]));

        $results = $engine->map([
            'hits' => [
                'total' => 1,
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => ['id' => 1],
                    ],
                ],
            ],
        ], $model);

        $this->assertEquals(1, count($results));
    }

    public function test_real_elasticsearch_update_and_search()
    {
        $engine = $this->getRealElasticEngine();
        $engine->update(Collection::make([new ElasticEngineTestModel]));
        $builder = new Builder(new ElasticEngineTestModel, '1');
        $builder->where('id', 1);
        $results = $engine->search($builder);
        $this->assertEquals(1, $results['hits']['total']);
        $this->assertEquals('1', $results['hits']['hits'][0]['_id']);
        $this->assertEquals(['id' => 1], $results['hits']['hits'][0]['_source']);
        $builder->where('title', 'zonda');
        $results = $engine->search($builder);
        $this->assertEquals(0, $results['hits']['total']);
    }

    public function test_real_elasticsearch_delete()
    {
        $engine = $this->getRealElasticEngine();
        $collection = Collection::make([new ElasticEngineTestModel]);
        $engine->update($collection);
        $builder = new Builder(new ElasticEngineTestModel, '1');
        $engine->delete($collection);
        $builder = new Builder(new ElasticEngineTestModel, '1');
        $results = $engine->search($builder);
        $this->assertEquals($results['hits']['total'], 0);
    }

    /**
     * @return \Gtk\Larasearch\Engines\ElasticEngine
     */
    protected function getRealElasticEngine()
    {
        $client = $this->getRealElasticClient();
        $this->markSkippedIfMissingElastic($client);
        $this->resetIndex($client);
        
        return new ElasticEngine($client, 'index_name');
    }
    /**
     * @return \Elasticsearch\Client
     */
    protected function getRealElasticClient()
    {
        return \Elasticsearch\ClientBuilder::create()
            ->setHosts(['127.0.0.1:9200'])
            ->setRetries(0)
            ->build();
    }

    /**
     * @param  \Elasticsearch\Client $client
     */
    protected function resetIndex(\Elasticsearch\Client $client)
    {
        $data = ['index' => 'index_name'];

        if ($client->indices()->exists($data)) {
            $client->indices()->delete($data);
        }

        $client->indices()->create($data);
    }

    /**
     * @param  \Elasticsearch\Client $client
     */
    protected function markSkippedIfMissingElastic(\Elasticsearch\Client $client)
    {
        try {
            $client->cluster()->health();
        } catch (\Elastic\Common\Exceptions\Curl\CouldNotConnectToHost $e) {
            $this->markTestSkipped('Could not connect to Elasticsearch');
        }
    }
}
