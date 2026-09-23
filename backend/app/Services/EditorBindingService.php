<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageEditorBinding;
use App\Models\Site;
use App\Models\SiteEditorBinding;
use LogicException;

class EditorBindingService
{
    private const PROVIDER = 'silex';

    public function __construct(private SilexClient $silexClient) {}

    /**
     * @return array{siteBinding: SiteEditorBinding, pageBinding: PageEditorBinding}
     */
    public function ensureBindings(Site $site, Page $page): array
    {
        $siteBinding = $site->editorBinding;

        if ($siteBinding === null) {
            $siteBinding = $site->editorBinding()->create([
                'provider' => self::PROVIDER,
                'external_website_id' => $this->silexClient->createWebsiteAndDetermineId($site->name),
            ]);
        }

        $pageBinding = $page->editorBinding;

        if ($pageBinding !== null) {
            if ($pageBinding->site_editor_binding_id !== $siteBinding->id) {
                throw new LogicException('Page editor binding belongs to a different site editor binding.');
            }

            return [
                'siteBinding' => $siteBinding,
                'pageBinding' => $pageBinding,
            ];
        }

        $pageBinding = $page->editorBinding()->create([
            'site_editor_binding_id' => $siteBinding->id,
            'external_page_id' => $page->editor_page_id,
        ]);

        return [
            'siteBinding' => $siteBinding,
            'pageBinding' => $pageBinding,
        ];
    }
}
