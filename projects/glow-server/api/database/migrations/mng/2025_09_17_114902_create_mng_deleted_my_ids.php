<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_deleted_my_ids', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('my_id', 255)->comment('MyID');
            $table->timestampsTz();

            $table->index('my_id', 'idx_my_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_deleted_my_ids');
    }
};
