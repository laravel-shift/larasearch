<?php

namespace Tests;

use Mockery;
use Tests\Fixtures\SearchableTestModel;

class SearchableTest extends AbstractTestCase
{
    public function test_searchable_using_update_is_called_on_collection()
    {
        $collection = Mockery::mock();
        $model = new SearchableTestModel();
        $collection->shouldReceive('isEmpty')->andReturn(false);
        $collection->shouldReceive('first->searchableUsing->update')->with($collection);
        $model->queueMakeSearchable($collection);
    }

    public function test_searchable_using_update_is_not_called_on_empty_collection()
    {
        $collection = Mockery::mock();
        $model = new SearchableTestModel();
        $collection->shouldReceive('isEmpty')->andReturn(true);
        $collection->shouldNotReceive('first->searchableUsing->update');
        $model->queueMakeSearchable($collection);
    }

    public function test_searchable_using_delete_is_called_on_collection()
    {
        $collection = Mockery::mock();
        $model = new SearchableTestModel();
        $collection->shouldReceive('isEmpty')->andReturn(false);
        $collection->shouldReceive('first->searchableUsing->delete')->with($collection);
        $model->queueRemoveFromSearch($collection);
    }

    public function test_searchable_using_delete_is_not_called_on_empty_collection()
    {
        $collection = Mockery::mock();
        $model = new SearchableTestModel();
        $collection->shouldReceive('isEmpty')->andReturn(true);
        $collection->shouldNotReceive('first->searchableUsing->delete');
        $model->queueRemoveFromSearch($collection);
    }

    public function test_make_all_searchable_uses_order_by()
    {
        ModelStubForMakeAllSearchable::makeAllSearchable();
    }

    public function test_make_all_unsearchable_uses_order_by()
    {
        ModelStubForMakeAllUnsearchable::removeAllFromSearch();
    }
}

class ModelStubForMakeAllSearchable extends SearchableTestModel
{
    public function newQuery()
    {
        $mock = Mockery::mock('Illuminate\Database\Eloquent\Builder');

        $mock->shouldReceive('orderBy')
            ->with('id')
            ->andReturnSelf()
            ->shouldReceive('searchable');

        return $mock;
    }
}

class ModelStubForMakeAllUnsearchable extends SearchableTestModel
{
    public function newQuery()
    {
        $mock = Mockery::mock('Illuminate\Database\Eloquent\Builder');

        $mock->shouldReceive('orderBy')
            ->with('id')
            ->andReturnSelf()
            ->shouldReceive('unsearchable');

        return $mock;
    }
}

namespace Gtk\Larasearch;

use Tests\Fixtures\SearchableTestModel;

function config($arg)
{
    return false;
}