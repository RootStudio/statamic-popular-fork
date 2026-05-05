<?php

namespace Tests\Pageviews;

use PHPUnit\Framework\Attributes\Test;
use ArthurPerton\Popular\Facades\Database;
use Carbon\Carbon;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Database::create(true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(Database::path());
    }

    #[Test]
    public function it_adds_a_pageview()
    {
        Carbon::setTestNow('2020-06-22');

        Database::addPageview('test');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(1, $records);
        $this->assertEquals('test', $records[0]->entry);
        $this->assertEquals(Carbon::now()->timestamp, $records[0]->timestamp);

        Database::delete();
    }

    #[Test]
    public function it_groups_pageviews()
    {
        Database::addPageview('foo');
        Database::addPageview('bar');
        Database::addPageview('foo');
        Database::addPageview('foo');
        Database::addPageview('bar');
        Database::addPageview('baz');

        [$records, $lastId] = Database::getGroupedPageviews();

        $this->assertEquals(6, $lastId);

        $this->assertCount(3, $records);
        $this->assertEquals(3, $records['foo']);
        $this->assertEquals(2, $records['bar']);
        $this->assertEquals(1, $records['baz']);

        Database::delete();
    }

    #[Test]
    public function it_deletes_pageviews()
    {
        Database::addPageview('test1');
        Database::addPageview('test2');
        Database::addPageview('test3');
        Database::addPageview('test4');
        Database::addPageview('test5');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(5, $records);

        Database::deletePageViews(3);
        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(2, $records);
    }

    #[Test]
    public function it_deletes_pageviews_for_an_entry()
    {
        Database::addPageview('foo');
        Database::addPageview('bar');
        Database::addPageview('foo');
        Database::addPageview('foo');
        Database::addPageview('bar');
        Database::addPageview('baz');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(6, $records);

        Database::deletePageViewsForEntry('foo');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(3, $records);

        Database::deletePageViewsForEntry('baz');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(2, $records);
        Database::deletePageViewsForEntry('bar');

        $records = Database::db()->select('SELECT * FROM pageview');
        $this->assertCount(0, $records);
    }
}
