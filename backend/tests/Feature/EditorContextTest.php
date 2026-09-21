<?php

namespace Tests\Feature;

use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_context_shows_the_selected_site_and_page(): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);

        $page = $site->pages()->create([
            'name' => 'Page A',
        ]);

        $response = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));

        $response->assertOk();
        $response->assertViewIs('editor.context');
        $response->assertViewHas('site', $site);
        $response->assertViewHas('page', $page);
        $response->assertSee('Site A');
        $response->assertSee('Page A');
        $response->assertSee($page->editor_page_id);
    }

    public function test_editor_context_rejects_a_page_from_another_site(): void
    {
        $siteA = Site::create([
            'name' => 'Site A',
        ]);

        $siteB = Site::create([
            'name' => 'Site B',
        ]);

        $pageB = $siteB->pages()->create([
            'name' => 'Page B',
        ]);

        $this->get(route('editor.context', [
            'site' => $siteA,
            'page' => $pageB,
        ]))->assertNotFound();
    }
}
