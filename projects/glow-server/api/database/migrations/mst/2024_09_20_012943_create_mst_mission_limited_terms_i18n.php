<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_mission_limited_terms_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_mission_limited_term_id', 255)->comment('mst_mission_limited_terms.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('description', 255)->comment('説明');
            $table->unique(['mst_mission_limited_term_id', 'language'], 'mst_mission_limited_term_id_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_limited_terms_i18n');
    }
};
