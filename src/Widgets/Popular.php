<?php

namespace ArthurPerton\Popular\Widgets;

use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Widgets\Widget;

class Popular extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        if (! optional(User::current())->can('view pageviews')) {
            return;
        }

        $collection = $this->config('collection');

        if (! Collection::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = Collection::findByHandle($collection);

        if (! optional(User::current())->can('view', $collection)) {
            return;
        }

        return view('popular::widgets.popular', [
            'collection' => $collection,
            'title' => $this->config('title', $collection->title()),
            'button' => $collection->createLabel(),
            'limit' => $this->config('limit', 5),
        ]);
    }
}
