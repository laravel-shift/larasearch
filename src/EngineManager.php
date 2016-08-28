<?php

namespace Gtk\Scout;

use Illuminate\Support\Manager;
use AlgoliaSearch\Client as Algolia;
use Gtk\Scout\Engines\NullEngine;
use Gtk\Scout\Engines\AlgoliaEngine;

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
     * @return \Gtk\Scout\Engines\AlgoliaEngine
     */
    public function createAlgoliaDriver()
    {
        return new AlgoliaEngine(new Algolia(
            config('scout.algolia.id'), config('scout.algolia.secret')
        ));
    }

    /**
     * Create a Null engine instance.
     *
     * @return \Gtk\Scout\Engines\NullEngine
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
