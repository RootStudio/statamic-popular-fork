<?php

namespace ArthurPerton\Popular\Pageviews;

use Exception;
use Statamic\Facades\File;
use Statamic\Facades\Path;

class LockingFile
{
    protected $filename;
    protected $attempts = 20;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function read(): mixed
    {
        if ($data = $this->readString()) {
            return unserialize($data);
        }

        return null;
    }

    public function readString(): string
    {
        $this->open();

        $serialized = $this->readStringFromStream();

        $this->close();

        return $serialized;
    }

    public function write($data): void
    {
        $this->writeString(serialize($data));
    }

    public function writeString(string $string): void
    {
        $this->open();

        $this->writeStringToStream($string);

        $this->close();
    }

    public function modify(callable $callback): void
    {
        $this->open();

        $data = $this->readDataFromStream();

        $data = $callback($data);

        $this->writeDataToStream($data);

        $this->close();
    }

    protected function open(): void
    {
        File::makeDirectory(Path::directory($this->filename));

        if (! $this->stream = fopen($this->filename, 'c+')) {
            throw new Exception("Unable to open file {$this->filename}");
        }

        if (! $this->lock()) {
            $this->close();

            throw new Exception("Unable to acquire a lock for file {$this->filename}");
        }
    }

    protected function lock(): bool
    {
        for ($i = 0; $i < $this->attempts; $i++) {
            if (flock($this->stream, LOCK_EX | LOCK_NB)) {
                return true;
            }

            usleep(round(rand(0, 10) * 1000)); // 0-10 milliseconds
        }

        return false;
    }

    protected function readDataFromStream(): mixed
    {
        $serialized = $this->readStringFromStream();

        return unserialize($serialized);
    }

    protected function writeDataToStream(mixed $data): void
    {
        $this->writeStringToStream(serialize($data));
    }

    protected function readStringFromStream(): string
    {
        return fgets($this->stream);
    }

    protected function writeStringToStream(string $string): void
    {
        ftruncate($this->stream, 0);
        rewind($this->stream);
        fwrite($this->stream, $string);
        fflush($this->stream);
    }

    protected function close(): void
    {
        fclose($this->stream); // NOTE this releases the lock too.
    }
}
