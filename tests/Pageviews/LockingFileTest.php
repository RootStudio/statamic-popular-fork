<?php

namespace Tests\Pageviews;

use PHPUnit\Framework\Attributes\Test;
use ArthurPerton\Popular\Pageviews\LockingFile;
use Tests\TestCase;

class LockingFileTest extends TestCase
{
    private $filename = __DIR__.'/locking-file.test';

    protected function setUp(): void
    {
        parent::setUp();

        @unlink($this->filename);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->filename);
    }

    #[Test]
    public function it_writes_a_string()
    {
        $file = $this->createFile();

        $file->writeString('foo');

        $this->assertStringMatchesFormatFile($this->filename, 'foo');
    }

    #[Test]
    public function it_reads_a_string()
    {
        $file = $this->createFile();

        $file->writeString('foo');

        $this->assertEquals('foo', $file->readString());
    }

    #[Test]
    public function it_writes_data()
    {
        $file = $this->createFile();

        $file->write(['foo' => 'bar']);

        $this->assertStringMatchesFormatFile($this->filename, 'a:1:{s:3:"foo";s:3:"bar";}');
    }

    #[Test]
    public function it_reads_data()
    {
        $file = $this->createFile();

        $file->write(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $file->read());
    }

    #[Test]
    public function it_modifies_data()
    {
        $file = $this->createFile();

        $file->write(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $file->read());

        $file->modify(function ($data) {
            $data['baz'] = 'quux';

            return $data;
        });

        $this->assertEquals(['foo' => 'bar', 'baz' => 'quux'], $file->read());
    }

    #[Test]
    public function it_does_not_read_from_a_locked_file()
    {
        $file = $this->createFile();

        $file->write(['foo' => 'bar']);

        $stream = fopen($this->filename, 'c+');
        flock($stream, LOCK_EX);

        $this->expectException(\Exception::class);

        $file->read();

        fclose($stream);
    }

    private function createFile()
    {
        return new LockingFile($this->filename);
    }
}
