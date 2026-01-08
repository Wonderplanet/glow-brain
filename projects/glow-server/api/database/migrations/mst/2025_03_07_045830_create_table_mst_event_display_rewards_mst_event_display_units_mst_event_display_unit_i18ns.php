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

        Schema::create('mst_event_display_rewards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_event_id')->default('');
            $table->string('resource_type')->default('');
            $table->string('resource_id')->default('');
            $table->integer('sort_order');
        });
        Schema::create('mst_event_display_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_event_id')->default('');
            $table->string('mst_unit_id')->default('');
        });
        Schema::create('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_event_display_unit_id')->default('');
            $table->enum('language', ['ja']);
            $table->string('serif1')->default('');
            $table->string('serif2')->default('');
            $table->string('serif3')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('mst_event_display_rewards');
        Schema::dropIfExists('mst_event_display_units');
        Schema::dropIfExists('mst_event_display_units_i18n');
    }
};
