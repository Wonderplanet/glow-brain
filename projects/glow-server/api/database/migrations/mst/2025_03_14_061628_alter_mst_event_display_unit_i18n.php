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
        //rename serif > speech_balloon_text
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('serif1', 'speech_balloon_text1');
            $table->renameColumn('serif2', 'speech_balloon_text2');
            $table->renameColumn('serif3', 'speech_balloon_text3');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //rename speech_balloon_text > serif
        Schema::table('mst_event_display_units_i18n', function (Blueprint $table) {
            $table->renameColumn('speech_balloon_text1', 'serif1');
            $table->renameColumn('speech_balloon_text2', 'serif2');
            $table->renameColumn('speech_balloon_text3', 'serif3');
        });
    }
};
