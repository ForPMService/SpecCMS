<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_editor_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('site_editor_binding_id')
                ->constrained('site_editor_bindings')
                ->cascadeOnDelete();
            $table->string('external_page_id');
            $table->timestamps();

            $table->unique('page_id');
            $table->unique(['site_editor_binding_id', 'external_page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_editor_bindings');
    }
};
