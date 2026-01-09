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
        Schema::table('mst_manga_animations', function (Blueprint $table) {
            $table->decimal('animation_speed', 3, 2)
                ->default(1.0)
                ->after('animation_start_delay')
                ->comment('アニメーションのスピード');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_manga_animations', function (Blueprint $table) {
            $table->dropColumn('animation_speed');
        });
    }
};
