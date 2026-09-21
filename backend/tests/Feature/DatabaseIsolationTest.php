<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIsolationTest extends TestCase
{
    public function test_phpunit_uses_the_isolated_postgresql_database(): void
    {
        $database = DB::selectOne('select current_database() as database')->database;

        $this->assertSame('speccms_testing', $database);
    }
}
