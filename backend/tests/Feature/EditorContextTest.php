<?php

namespace Tests\Feature;

use App\Models\PageEditorBinding;
use App\Models\Site;
use App\Models\SiteEditorBinding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Tests\TestCase;

class EditorContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_context_redirects_to_silex_for_selected_site_and_page(): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);
        $page = $site->pages()->create([
            'name' => 'Page A',
        ]);

        $this->fakeWebsiteCreation(['existing-website'], ['existing-website', 'new-website']);

        $response = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));

        $response->assertRedirect(
            'http://localhost:6805/?id=new-website&lang=en&connectorId=fs-storage&pageId='
            .$page->editor_page_id.'&pageName=Page%20A'
        );
        $this->assertDatabaseHas('site_editor_bindings', [
            'site_id' => $site->id,
            'provider' => 'silex',
            'external_website_id' => 'new-website',
        ]);
        $this->assertDatabaseHas('page_editor_bindings', [
            'page_id' => $page->id,
            'external_page_id' => $page->editor_page_id,
        ]);
        Http::assertSentCount(3);
        Http::assertSent(function (Request $request): bool {
            $payload = json_decode($request->body());

            return $request->method() === 'PUT'
                && $request->url() === 'http://silex:6805/api/website?connectorId=fs-storage'
                && $payload instanceof stdClass
                && $payload->name === 'Site A'
                && $payload->connectorUserSettings instanceof stdClass
                && get_object_vars($payload->connectorUserSettings) === [];
        });
    }

    public function test_editor_context_uses_existing_bindings_without_creating_a_website(): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);
        $page = $site->pages()->create([
            'name' => 'Page A',
        ]);
        $siteBinding = SiteEditorBinding::create([
            'site_id' => $site->id,
            'provider' => 'silex',
            'external_website_id' => 'existing-website',
        ]);
        PageEditorBinding::create([
            'page_id' => $page->id,
            'site_editor_binding_id' => $siteBinding->id,
            'external_page_id' => 'existing-page',
        ]);
        Http::preventStrayRequests();

        $response = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));

        $response->assertRedirect(
            'http://localhost:6805/?id=existing-website&lang=en&connectorId=fs-storage&pageId=existing-page&pageName=Page%20A'
        );
        $this->assertDatabaseCount('site_editor_bindings', 1);
        $this->assertDatabaseCount('page_editor_bindings', 1);
        Http::assertNothingSent();
    }

    public function test_editor_context_reuses_bindings_when_the_same_page_is_opened_again(): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);
        $page = $site->pages()->create([
            'name' => 'Page A',
        ]);
        $this->fakeWebsiteCreation(['existing-website'], ['existing-website', 'new-website']);

        $firstResponse = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));
        $secondResponse = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));

        $firstResponse->assertRedirect();
        $secondResponse->assertRedirect();
        $this->assertDatabaseCount('site_editor_bindings', 1);
        $this->assertDatabaseCount('page_editor_bindings', 1);
        Http::assertSentCount(3);
    }

    public function test_editor_context_creates_one_website_for_two_pages_of_the_same_site(): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);
        $firstPage = $site->pages()->create([
            'name' => 'Page A',
        ]);
        $secondPage = $site->pages()->create([
            'name' => 'Page B',
        ]);
        $this->fakeWebsiteCreation(['existing-website'], ['existing-website', 'new-website']);

        $firstResponse = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $firstPage,
        ]));
        $secondResponse = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $secondPage,
        ]));

        $firstResponse->assertRedirect(
            'http://localhost:6805/?id=new-website&lang=en&connectorId=fs-storage&pageId='
            .$firstPage->editor_page_id.'&pageName=Page%20A'
        );
        $secondResponse->assertRedirect(
            'http://localhost:6805/?id=new-website&lang=en&connectorId=fs-storage&pageId='
            .$secondPage->editor_page_id.'&pageName=Page%20B'
        );
        $this->assertDatabaseCount('site_editor_bindings', 1);
        $this->assertDatabaseCount('page_editor_bindings', 2);
        $this->assertDatabaseHas('page_editor_bindings', [
            'page_id' => $firstPage->id,
            'external_page_id' => $firstPage->editor_page_id,
        ]);
        $this->assertDatabaseHas('page_editor_bindings', [
            'page_id' => $secondPage->id,
            'external_page_id' => $secondPage->editor_page_id,
        ]);
        Http::assertSentCount(3);
    }

    #[DataProvider('ambiguousWebsiteIdLists')]
    public function test_editor_context_does_not_bind_an_ambiguous_created_website(array $websiteIdsAfter): void
    {
        $site = Site::create([
            'name' => 'Site A',
        ]);
        $page = $site->pages()->create([
            'name' => 'Page A',
        ]);
        $this->fakeWebsiteCreation(['existing-website'], $websiteIdsAfter);

        $response = $this->get(route('editor.context', [
            'site' => $site,
            'page' => $page,
        ]));

        $response->assertServerError();
        $this->assertDatabaseMissing('site_editor_bindings', [
            'site_id' => $site->id,
        ]);
        $this->assertDatabaseMissing('page_editor_bindings', [
            'page_id' => $page->id,
        ]);
        Http::assertSentCount(3);
        Http::assertNotSent(static fn (Request $request): bool => $request->method() === 'DELETE');
    }

    public function test_editor_context_does_not_rebind_a_page_to_a_different_site_binding(): void
    {
        $siteA = Site::create([
            'name' => 'Site A',
        ]);
        $siteB = Site::create([
            'name' => 'Site B',
        ]);
        $page = $siteA->pages()->create([
            'name' => 'Page A',
        ]);
        SiteEditorBinding::create([
            'site_id' => $siteA->id,
            'provider' => 'silex',
            'external_website_id' => 'website-a',
        ]);
        $siteBindingB = SiteEditorBinding::create([
            'site_id' => $siteB->id,
            'provider' => 'silex',
            'external_website_id' => 'website-b',
        ]);
        $pageBinding = PageEditorBinding::create([
            'page_id' => $page->id,
            'site_editor_binding_id' => $siteBindingB->id,
            'external_page_id' => 'page-b',
        ]);
        Http::preventStrayRequests();

        $response = $this->get(route('editor.context', [
            'site' => $siteA,
            'page' => $page,
        ]));

        $response->assertServerError();
        $this->assertDatabaseHas('page_editor_bindings', [
            'id' => $pageBinding->id,
            'site_editor_binding_id' => $siteBindingB->id,
        ]);
        $this->assertDatabaseCount('site_editor_bindings', 2);
        Http::assertNothingSent();
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
        Http::preventStrayRequests();

        $this->get(route('editor.context', [
            'site' => $siteA,
            'page' => $pageB,
        ]))->assertNotFound();

        Http::assertNothingSent();
    }

    /**
     * @return array<string, array{array<int, string>}>
     */
    public static function ambiguousWebsiteIdLists(): array
    {
        return [
            'no new website id' => [['existing-website']],
            'multiple new website ids' => [['existing-website', 'new-website-a', 'new-website-b']],
        ];
    }

    /**
     * @param  array<int, string>  $websiteIdsBefore
     * @param  array<int, string>  $websiteIdsAfter
     */
    private function fakeWebsiteCreation(array $websiteIdsBefore, array $websiteIdsAfter): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'http://silex:6805/api/website*' => Http::sequence()
                ->push($this->websiteListResponse($websiteIdsBefore))
                ->push('', 204)
                ->push($this->websiteListResponse($websiteIdsAfter)),
        ]);
    }

    /**
     * @param  array<int, string>  $websiteIds
     */
    private function websiteListResponse(array $websiteIds): string
    {
        return json_encode(array_map(static fn (string $websiteId): array => [
            'websiteId' => $websiteId,
            'name' => "Website {$websiteId}",
            'connectorUserSettings' => new stdClass,
            'createdAt' => '1970-01-01T00:00:00.000Z',
            'updatedAt' => '2026-09-23T13:33:35.219Z',
        ], $websiteIds), JSON_THROW_ON_ERROR);
    }
}
