<?php

namespace Gtk\Larasearch;

use Illuminate\Pagination\Paginator as IlluminatePaginator;

class Paginator extends IlluminatePaginator
{
    /**
     * Manually indicate that the paginator does have more pages.
     *
     * @param  bool  $value
     * @return $this
     */
    public function hasMorePagesWhen($value = true)
    {
        $this->hasMore = $value;

        return $this;
    }
}