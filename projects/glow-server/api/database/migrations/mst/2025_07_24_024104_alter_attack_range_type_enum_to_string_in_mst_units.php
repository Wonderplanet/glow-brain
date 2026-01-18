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
        Schema::table('mst_units', function (Blueprint $table) {
            $table->string('attack_range_type', 255)->comment('ロール')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->enum('attack_range_type', ['Short','Middle','Long'])->comment('ロール')->change();
        });
    }
};
