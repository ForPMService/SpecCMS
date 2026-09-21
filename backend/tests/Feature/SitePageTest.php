<?php

namespace Tests\Feature;

use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SitePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_page_model_and_database_constraints(): void
    {
        $site = Site::create([
            'name' => 'Marketing site',
        ]);

        $page = $site->pages()->create([
            'name' => 'Home',
        ]);

        $secondPage = $site->pages()->create([
            'name' => 'About',
        ]);

        $this->assertTrue($site->exists);
        $this->assertSame('Marketing site', $site->name);
        $this->assertSame($site->id, $page->site_id);
        $this->assertTrue($page->site->is($site));
        $this->assertTrue($site->pages->contains($page));
        $this->assertCount(2, $site->pages);

        $this->assertNotEmpty($page->editor_page_id);
        $this->assertTrue(Str::isUlid($page->editor_page_id));
        $this->assertNotSame($page->editor_page_id, $secondPage->editor_page_id);
        $this->assertIsNumeric($page->id);
        $this->assertSame('id', $page->getKeyName());
        $this->assertSame('int', $page->getKeyType());
        $this->assertTrue($page->getIncrementing());

        $editorPageId = $page->editor_page_id;
        $page->name = 'Updated home';
        $page->save();
        $page->refresh();

        $this->assertSame($editorPageId, $page->editor_page_id);

        $idColumn = DB::selectOne(<<<'SQL'
            SELECT data_type, is_nullable, column_default, is_identity
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = 'pages'
              AND column_name = 'id'
        SQL);

        $this->assertSame('bigint', $idColumn->data_type);
        $this->assertSame('NO', $idColumn->is_nullable);
        $this->assertTrue(
            $idColumn->is_identity === 'YES'
            || str_contains((string) $idColumn->column_default, 'nextval')
        );

        $editorPageIdColumn = DB::selectOne(<<<'SQL'
            SELECT data_type, character_maximum_length, is_nullable
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = 'pages'
              AND column_name = 'editor_page_id'
        SQL);

        $this->assertSame('character', $editorPageIdColumn->data_type);
        $this->assertSame('26', (string) $editorPageIdColumn->character_maximum_length);
        $this->assertSame('NO', $editorPageIdColumn->is_nullable);

        $primaryKeyColumns = DB::select(<<<'SQL'
            SELECT key_column_usage.column_name
            FROM information_schema.table_constraints AS table_constraints
            JOIN information_schema.key_column_usage AS key_column_usage
              ON key_column_usage.constraint_name = table_constraints.constraint_name
             AND key_column_usage.table_schema = table_constraints.table_schema
             AND key_column_usage.table_name = table_constraints.table_name
            WHERE table_constraints.table_schema = 'public'
              AND table_constraints.table_name = 'pages'
              AND table_constraints.constraint_type = 'PRIMARY KEY'
            ORDER BY key_column_usage.ordinal_position
        SQL);

        $this->assertSame(['id'], array_map(
            static fn (object $column): string => $column->column_name,
            $primaryKeyColumns
        ));

        $uniqueEditorIndexes = DB::select(<<<'SQL'
            SELECT indexname
            FROM pg_indexes
            WHERE schemaname = 'public'
              AND tablename = 'pages'
              AND indexdef ILIKE 'CREATE UNIQUE INDEX%'
              AND indexdef ILIKE '%(editor_page_id)%'
        SQL);

        $this->assertNotEmpty($uniqueEditorIndexes);

        try {
            DB::table('pages')->insert([
                'site_id' => $site->id,
                'name' => 'Duplicate editor page id',
                'editor_page_id' => $page->editor_page_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->fail('PostgreSQL accepted a duplicate editor_page_id.');
        } catch (QueryException $exception) {
            $this->assertSame('23505', (string) $exception->getCode());
            $this->assertStringContainsString('editor_page_id', $exception->getMessage());
        }
    }
}
