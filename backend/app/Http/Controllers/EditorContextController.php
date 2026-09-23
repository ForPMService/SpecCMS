<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Site;
use App\Services\EditorBindingService;
use App\Services\SilexClient;
use Illuminate\Http\RedirectResponse;

class EditorContextController extends Controller
{
    public function __invoke(
        Site $site,
        Page $page,
        EditorBindingService $editorBindingService,
        SilexClient $silexClient,
    ): RedirectResponse {
        $bindings = $editorBindingService->ensureBindings($site, $page);

        return redirect()->away($silexClient->buildEditorUrl(
            $bindings['siteBinding']->external_website_id,
            $bindings['pageBinding']->external_page_id,
            $page->name,
        ));
    }
}
