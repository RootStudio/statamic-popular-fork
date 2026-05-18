<?php

namespace ArthurPerton\Popular\Config;

class Config
{
    protected $collections;

    public function __construct()
    {
        $this->collections = collect();
    }

    public function addField(): bool
    {
        return (bool) config('popular.add_field', true);
    }

    public function collectionIncluded(string $handle): bool
    {
        if (! is_null($include = $this->collections->get($handle))) {
            return $include;
        }

        $excludes = config('popular.exclude_collections') ?? [];
        $includes = config('popular.include_collections') ?? ['*'];

        $include = ! $this->match($excludes, $handle) && $this->match($includes, $handle);

        $this->collections->put($handle, $include);

        return $include;
    }

    private function match(array $patterns, string $handle): bool
    {
        if (in_array('*', $patterns)) {
            return true;
        }

        return collect($patterns)->contains(function ($pattern) use ($handle) {
            if ($handle === $pattern) {
                return true;
            }

            if (! str_contains($pattern, '*')) {
                return false;
            }

            return preg_match('/^'.str_replace('*', '.*', $pattern).'$/', $handle);
        });
    }
}
