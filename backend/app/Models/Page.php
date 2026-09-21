<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['site_id', 'name'])]
class Page extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            $page->editor_page_id ??= (string) Str::ulid();
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
