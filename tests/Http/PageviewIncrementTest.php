<?php

namespace Tests\Http;

use PHPUnit\Framework\Attributes\Test;
use ArthurPerton\Popular\Facades\Database;
use ArthurPerton\Popular\Facades\Pageviews;
use ArthurPerton\Popular\Http\Controllers\PageviewController;
use ArthurPerton\Popular\Pageviews\Aggregator;
use Statamic\Facades\Path;
use Tests\TestCase;

class PageviewIncrementTest extends TestCase
{
    protected string $repoFile;

    protected function defineRoutes($router): void
    {
        $router->post('/!/popular/pageviews', [PageviewController::class, 'store']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:' . base64_encode(random_bytes(32)),
            'popular.files' => storage_path('popular'),
        ]);

        $this->repoFile = Path::assemble(config('popular.files'), 'pageviews');
        @unlink($this->repoFile);

        Database::create(true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(Database::path());
        @unlink($this->repoFile);
    }

    #[Test]
    public function posting_to_the_pageview_endpoint_increments_the_count()
    {
        $this->assertEquals(0, Pageviews::get('entry-1'));

        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/!/popular/pageviews', ['entry' => 'entry-1']);

        $response->assertSuccessful();

        (new Aggregator)->aggregate();

        $this->assertEquals(1, Pageviews::get('entry-1'));
    }

    #[Test]
    public function multiple_posts_accumulate_correctly()
    {
        $post = fn ($data) => $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/!/popular/pageviews', $data);

        $post(['entry' => 'entry-1']);
        $post(['entry' => 'entry-2']);
        $post(['entry' => 'entry-1']);
        $post(['entry' => 'entry-1']);

        (new Aggregator)->aggregate();

        $this->assertEquals(3, Pageviews::get('entry-1'));
        $this->assertEquals(1, Pageviews::get('entry-2'));
    }

    #[Test]
    public function posting_without_an_entry_id_does_not_record_a_pageview()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/!/popular/pageviews', []);

        (new Aggregator)->aggregate();

        $this->assertEquals(0, Pageviews::get(''));
        $this->assertEquals(0, count(Pageviews::all()));
    }

    #[Test]
    public function count_is_zero_before_aggregation_runs()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/!/popular/pageviews', ['entry' => 'entry-1']);

        // Aggregator has NOT run yet — repository should still be empty
        $this->assertEquals(0, Pageviews::get('entry-1'));
    }

    #[Test]
    public function running_aggregation_multiple_times_does_not_double_count()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/!/popular/pageviews', ['entry' => 'entry-1']);

        (new Aggregator)->aggregate();
        (new Aggregator)->aggregate(); // second run — queue is already empty

        $this->assertEquals(1, Pageviews::get('entry-1'));
    }
}
