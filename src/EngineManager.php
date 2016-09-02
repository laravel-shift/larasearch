<?php

namespace Gtk\Larasearch;

use Illuminate\Support\Manager;
use Gtk\Larasearch\Engines\NullEngine;
use AlgoliaSearch\Client as Algolia;
use Gtk\Larasearch\Engines\AlgoliaEngine;
use Gtk\Larasearch\Engines\ElasticEngine;

class EngineManager extends Manager
{
    /**
     * Get a driver instance.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function engine($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Create an Algolia engine instance.
     *
     * @return \Gtk\Larasearch\Engines\AlgoliaEngine
     */
    public function createAlgoliaDriver()
    {
        return new AlgoliaEngine(new Algolia(
            config('scout.algolia.id'), config('scout.algolia.secret')
        ));
    }

    /**
     * Create an Elastic engine instance.
     *
     * @return \Gtk\Larasearch\Engines\ElasticEngine
     */
    public function createElasticDriver()
    {
        return new ElasticEngine(
            ElasticEngine::buildClient(config('scout.elastic')), config('scout.elastic.index')
        );
    }

    /**
     * Create a Null engine instance.
     *
     * @return \Gtk\Larasearch\Engines\NullEngine
     */
    public function createNullDriver()
    {
        return new NullEngine;
    }

    /**
     * Get the default session driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['scout.driver'];
    }
}
