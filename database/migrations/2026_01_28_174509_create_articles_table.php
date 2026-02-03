<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->text('url')->unique();
            $table->text('image_url')->nullable();
            $table->foreignId('source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'source_id']);
        });

        // Add full-text search tsvector column
        DB::statement("
            ALTER TABLE articles
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('english',
                    coalesce(title, '') || ' ' ||
                    coalesce(description, '') || ' ' ||
                    coalesce(content, '')
                )
            ) STORED
        ");

        // Create a GIN index on the tsvector column for fast searching
        DB::statement("CREATE INDEX articles_search_vector_idx ON articles USING GIN (search_vector)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS articles_search_vector_idx");
        Schema::dropIfExists('articles');
    }
};
