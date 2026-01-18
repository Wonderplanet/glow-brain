<?php

declare(strict_types=1);

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
        Schema::table('mst_exchanges', function (Blueprint $table) {
            $table->string('mst_event_id', 255)->nullable()->after('id')->comment('mst_events.id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_exchanges', function (Blueprint $table) {
            $table->dropColumn('mst_event_id');
        });
    }
};
