<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'site_editor_binding_id', 'external_page_id'])]
class PageEditorBinding extends Model
{
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function siteEditorBinding(): BelongsTo
    {
        return $this->belongsTo(SiteEditorBinding::class);
    }
}
