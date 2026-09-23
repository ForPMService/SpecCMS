<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name'])]
class Site extends Model
{
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function editorBinding(): HasOne
    {
        return $this->hasOne(SiteEditorBinding::class);
    }
}
