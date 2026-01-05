<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 新規テーブル追加
    // - name: MstMissionBeginnerPromptPhraseI18n
    // obscure: true
    // params:
    //   - name: id
    //     type: string
    //   - name: language
    //     type: Language
    //   - name: promptPhraseText
    //     type: string
    //   - name: startAt
    //     type: DateTimeOffset
    //   - name: endAt
    //     type: DateTimeOffset

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_mission_beginner_prompt_phrases_i18n', function (Blueprint $table) {
            $table->string('id', 255);
            $table->enum('language', ['ja']);
            $table->text('prompt_phrase_text');
            $table->timestampTz('start_at')->nullable(false);
            $table->timestampTz('end_at')->nullable(false);
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_beginner_prompt_phrases_i18n');
    }
};
