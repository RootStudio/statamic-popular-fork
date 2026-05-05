<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use ArthurPerton\Popular\Facades\Pageviews;
use ArthurPerton\Popular\Tags\PageviewCount;
use Statamic\Fields\Value;
use Tests\TestCase;

class PageviewCountTest extends TestCase
{
    #[Test]
    public function it_throws_an_exception_when_no_id_is_provided()
    {
        $tag = new PageviewCount();

        $this->expectException(\Exception::class);

        $tag->index();
    }

    #[Test]
    public function it_gets_the_id_from_the_parameter_when_given()
    {
        $tag = new PageviewCount();

        $tag->setContext(['id' => 'foo']);
        $tag->setParameters(['id' => 'bar']);

        Pageviews::shouldReceive('get')->once()->with('bar')->andReturn(42);

        $this->assertEquals(42, $tag->index());
    }

    #[Test]
    public function it_gets_the_id_from_a_value_parameter()
    {
        $tag = new PageviewCount();

        $tag->setContext([]);
        $tag->setParameters(['id' => new Value('foo')]);

        Pageviews::shouldReceive('get')->once()->with('foo')->andReturn(42);

        $this->assertEquals(42, $tag->index());
    }

    #[Test]
    public function it_gets_the_id_from_the_context()
    {
        $tag = new PageviewCount();

        $tag->setContext(['id' => 'foo']);
        $tag->setParameters([]);

        Pageviews::shouldReceive('get')->once()->with('foo')->andReturn(42);

        $this->assertEquals(42, $tag->index());
    }

    #[Test]
    public function it_gets_the_id_from_a_variable()
    {
        $tag = new PageviewCount();

        $tag->setContext(['variable' => 'foo']);
        $tag->setParameters([':id' => 'variable']);

        Pageviews::shouldReceive('get')->once()->with('foo')->andReturn(42);

        $this->assertEquals(42, $tag->index());
    }

    #[Test]
    public function it_returns_an_array_when_it_is_a_tag_pair()
    {
        $tag = new PageviewCount();

        $tag->isPair = true;

        $tag->setContext(['id' => 'foo']);

        Pageviews::shouldReceive('get')->once()->with('foo')->andReturn(42);

        $this->assertEquals(['pageviews' => 42], $tag->index());
    }
}
