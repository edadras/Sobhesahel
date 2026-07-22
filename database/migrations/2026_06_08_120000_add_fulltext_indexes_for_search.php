<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addFullTextIndex('news', 'news_search_fulltext', ['title', 'short_description']);
        $this->addFullTextIndex('galleries', 'galleries_search_fulltext', ['title', 'short_description']);
        $this->addFullTextIndex('videos', 'videos_search_fulltext', ['title', 'short_description']);
        $this->addFullTextIndex('notes', 'notes_search_fulltext', ['title', 'short_description']);
    }

    public function down(): void
    {
        $this->dropFullTextIndex('news', 'news_search_fulltext');
        $this->dropFullTextIndex('galleries', 'galleries_search_fulltext');
        $this->dropFullTextIndex('videos', 'videos_search_fulltext');
        $this->dropFullTextIndex('notes', 'notes_search_fulltext');
    }

    private function addFullTextIndex(string $table, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        $columnList = implode(', ', array_map(fn (string $column) => "`{$column}`", $columns));

        DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT INDEX `{$indexName}` ({$columnList})");
    }

    private function dropFullTextIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }
};
