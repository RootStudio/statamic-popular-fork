<?php

namespace ArthurPerton\Popular\Pageviews;

use Illuminate\Support\Collection;
use Statamic\Facades\Path;

class Repository
{
    protected $items;
    protected $file;

    public function __construct()
    {
        $this->file = new LockingFile(Path::assemble(config('popular.files'), 'pageviews'));
    }

    public function get(string $entry): int
    {
        return (int) $this->items()->get($entry, 0);
    }

    public function all(): Collection
    {
        return $this->items();
    }

    public function addMultiple(array $pageviews): void
    {
        $this->assertEntryMap($pageviews);

        $this->update(function ($items) use ($pageviews) {
            collect($pageviews)->each(function ($views, $entry) use ($items) {
                $items->put($entry, (int) $items->get($entry, 0) + $views);
            });

            return $items;
        });
    }

    public function setMultiple(array $pageviews): void
    {
        $this->assertEntryMap($pageviews);

        $this->update(function ($items) use ($pageviews) {
            return $items->merge($pageviews);
        });
    }

    public function deleteMultiple(array $ids): void
    {
        $this->update(function ($items) use ($ids) {
            return $items->forget($ids);
        });
    }

    protected function update(callable $callback): void
    {
        $this->file->modify(function ($data) use ($callback) {
            $this->items = $callback(collect($data ?: []));

            return $this->items->all();
        });
    }

    protected function items(): Collection
    {
        if (! $this->items) {
            $this->items = collect($this->file->read());
        }

        return $this->items;
    }

    protected function assertEntryMap(array $pageviews): void
    {
        if (array_is_list($pageviews)) {
            throw new \InvalidArgumentException('Pageviews must be keyed by entry ID. List-style arrays are not allowed.');
        }
    }
}
