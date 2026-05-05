<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use ArthurPerton\Popular\Modifiers\Shorten;
use Tests\TestCase;

class ShortenTest extends TestCase
{
    #[Test]
    public function it_shortens_numbers()
    {
        $modifier = new Shorten();

        $this->assertEquals('12', $modifier->index(12, null, null));
        $this->assertEquals('500', $modifier->index(500, null, null));
        $this->assertEquals('999', $modifier->index(999, null, null));
        $this->assertEquals('1K', $modifier->index(1000, null, null));
        $this->assertEquals('1.4K', $modifier->index(1420, null, null));
        $this->assertEquals('1.5K', $modifier->index(1499, null, null));
        $this->assertEquals('35K', $modifier->index(35251, null, null));
        $this->assertEquals('295K', $modifier->index(295018, null, null));
        $this->assertEquals('5M', $modifier->index(4981728, null, null));
        $this->assertEquals('5.1M', $modifier->index(5081728, null, null));
        $this->assertEquals('1B', $modifier->index(1E9, null, null));
        $this->assertEquals('1T', $modifier->index(1E12, null, null));
        $this->assertEquals('1.1T', $modifier->index(1E12 + 1E11, null, null));
        $this->assertEquals('1000000T', $modifier->index(1E18, null, null));
    }
}
