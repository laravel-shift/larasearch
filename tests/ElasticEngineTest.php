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
        $client->shouldReceive('bulk');

        $engine = new ElasticEngine($client, 'index_name');
        $engine->update(Collection::make([new ElasticEngineTestModel]));
    }

    public function test_delete_removes_objects_to_index()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk');

        $engine = new ElasticEngine($client, 'my_index');
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
}
