<?php

namespace ArthurPerton\Popular\Listeners;

use ArthurPerton\Popular\Facades\Config;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\User;

class AddPageviewsField
{
    public function handle(EntryBlueprintFound $event)
    {
        if (!$user = User::current()) {
            return;
        }

        if (!Config::addField()) {
            return;
        }

        if (!$user->can('view pageviews')) {
            return;
        }

        if (!$blueprint = $event->blueprint) {
            return;
        }

        if ($blueprint->hasField('pageviews')) {
            return;
        }

        if (!$collection = $this->getCollection($blueprint)) {
            return;
        }

        if (!Config::collectionIncluded($collection->handle())) {
            return;
        }

        $contents = $blueprint->contents();

        if (isset($contents['tabs'])) {
            $this->addFieldTabs($contents);
        } elseif (isset($contents['sections'])) {
            $this->addFieldNoTabs($contents);
        } else {
            return;
        }

        $blueprint->setContents($contents);
    }

    private function addFieldTabs(&$contents) // V4
    {
        $tab = isset($contents['tabs']['sidebar']) ? 'sidebar' : array_key_first($contents['tabs']);

        if (! isset($contents['tabs'][$tab]['sections'])) {
            $contents['tabs'][$tab]['sections'] = [];
        }

        $contents['tabs'][$tab]['sections'][] = [
            'fields' => [
                $this->field(),
            ],
        ];
    }

    private function addFieldNoTabs(&$contents) // V3
    {
        $section = isset($contents['sections']['sidebar']) ? 'sidebar' : array_key_first($contents['sections']);

        if (! isset($contents['sections'][$section]['fields'])) {
            $contents['sections'][$section]['fields'] = [];
        }

        $contents['sections'][$section]['fields'][] = $this->field();
    }

    private function getCollection($blueprint)
    {
        $parent = $blueprint->parent();

        if ($parent instanceof Entry) {
            return $parent->collection();
        } elseif ($parent instanceof Collection) {
            return $parent;
        } else {
            return null;
        }
    }

    private function field()
    {
        return [
            'handle' => 'pageviews',
            'field' => [
                'type' => 'pageviews',
                'visibility' => 'computed',
                'editable' => User::current()->can('edit pageviews'),
            ],
        ];
    }
}
