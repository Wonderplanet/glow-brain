<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('usr_artwork_fragments');
        Schema::create('usr_artwork_fragments', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_artwork_id', 255)->comment('mst_artworks.id');
            $table->string('mst_artwork_fragment_id', 255)->comment('mst_artwork_fragments.id');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'mst_artwork_id']);
            $table->primary(['usr_user_id', 'mst_artwork_fragment_id']);
        });

        Schema::dropIfExists('usr_artworks');
        Schema::create('usr_artworks', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_artwork_id', 255)->comment('mst_artworks.id');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_artwork_id']);
        });

        Schema::dropIfExists('usr_cheat_sessions');
        Schema::create('usr_cheat_sessions', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('content_type', 255)->comment('コンテンツのタイプ');
            $table->string('target_id', 255)->nullable()->comment('降臨バトルの場合はmst_advent_battles.id');
            $table->json('party_status')->nullable()->comment('パーティステータス');
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_condition_packs');
        Schema::create('usr_condition_packs', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_pack_id', 255)->comment('mst_packs.id');
            $table->timestampTz('start_date')->comment('購入可能期間開始日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_pack_id']);
        });

        // 未使用
        Schema::dropIfExists('usr_device_link_passwords');
        Schema::create('usr_device_link_passwords', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('auth_id', 256)->unique();
            $table->string('auth_password', 256);
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        // 未使用
        Schema::dropIfExists('usr_device_link_socials');
        Schema::create('usr_device_link_socials', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->tinyInteger('auth_type');
            $table->string('auth_token', 256);
            $table->timestampsTz();

            $table->unique(['auth_type', 'auth_token'], 'auth_type_auth_token');
            $table->unique(['usr_user_id', 'auth_type'], 'uk_usr_user_id_auth_type');
            $table->primary(['usr_user_id', 'auth_type']);
        });

        // usr_devicesはusr_user_idで検索していないのでスキップ

        Schema::dropIfExists('usr_emblems');
        Schema::create('usr_emblems', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_emblem_id', 255);
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_emblem_id']);
        });

        Schema::dropIfExists('usr_gacha_uppers');
        Schema::create('usr_gacha_uppers', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('ユーザーID');
            $table->string('upper_group', 255)->comment('天井設定区分');
            $table->string('upper_type', 255)->default('')->comment('天井タイプ');
            $table->unsignedInteger('count')->default(0)->comment('天井を保証する回数 リセット条件に合致した場合カウントは0に戻る');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'upper_group', 'upper_type']);
        });

        Schema::dropIfExists('usr_gachas');
        Schema::create('usr_gachas', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->timestampTz('ad_played_at')->nullable()->default(null)->comment('広告で回した時間');
            $table->timestampTz('played_at')->nullable()->default(null)->comment('回した時間');
            $table->unsignedInteger('ad_count')->default(0)->comment('広告でガチャを回した回数');
            $table->unsignedInteger('ad_daily_count')->default(0)->comment('広告で本日ガチャを回した回数');
            $table->unsignedInteger('count')->default(0)->comment('ガチャを回した回数');
            $table->unsignedInteger('daily_count')->default(0)->comment('日次でガチャを回した回数');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'opr_gacha_id']);
        });

        Schema::dropIfExists('usr_idle_incentives');
        Schema::create('usr_idle_incentives', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->unsignedInteger('diamond_quick_receive_count')->default(0);
            $table->unsignedInteger('ad_quick_receive_count')->default(0)->comment('広告でのクイック獲得回数');
            $table->timestampTz('idle_started_at')->comment('放置開始時間');
            $table->timestampTz('diamond_quick_receive_at')->default('2000-01-01 00:00:00');
            $table->timestampTz('ad_quick_receive_at')->comment('広告でクイック獲得を実行した時刻');
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_items');
        Schema::create('usr_items', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_item_id', 255)->default('0');
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_item_id']);
        });

        // usr_messagesはusr_user_idと組み合わせて一意になるPKがなさそうなのでスキップ

        Schema::dropIfExists('usr_mission_achievement_progresses');
        Schema::create('usr_mission_achievement_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー「criterion_type:criterion_value」');
            $table->unsignedBigInteger('progress')->comment('生涯累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_mission_achievements');
        Schema::create('usr_mission_achievements', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_achievement_id', 255)->comment('mst_mission_achievements.id');
            $table->unsignedTinyInteger('status')->comment('0:未クリア, 1:クリア, 2:報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_achievement_id']);
        });

        Schema::dropIfExists('usr_mission_beginner_progresses');
        Schema::create('usr_mission_beginner_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('初心者ミッション累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_mission_beginners');
        Schema::create('usr_mission_beginners', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_beginner_id', 255)->comment('mst_mission_beginners.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_beginner_id']);
        });

        Schema::dropIfExists('usr_mission_dailies');
        Schema::create('usr_mission_dailies', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_daily_id', 255)->comment('mst_mission_dailies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_daily_id']);
        });

        Schema::dropIfExists('usr_mission_daily_bonuses');
        Schema::create('usr_mission_daily_bonuses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_daily_bonus_id', 255)->comment('mst_mission_daily_bonuses.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_daily_bonus_id']);
        });

        Schema::dropIfExists('usr_mission_daily_progresses');
        Schema::create('usr_mission_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_mission_event_dailies');
        Schema::create('usr_mission_event_dailies', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_daily_id', 255)->comment('mst_mission_event_dailies.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
            $table->primary(['usr_user_id', 'mst_mission_event_daily_id']);
        });

        Schema::dropIfExists('usr_mission_event_daily_bonus_progresses');
        Schema::create('usr_mission_event_daily_bonus_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_schedule_id', 255)->comment('mst_mission_event_daily_bonus_schedules.id');
            $table->unsignedInteger('progress')->comment('ログイン回数進捗');
            $table->timestampTz('latest_update_at')->nullable()->comment('ログイン更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_mission_event_daily_bonus_schedule_id']);
        });

        Schema::dropIfExists('usr_mission_event_daily_bonuses');
        Schema::create('usr_mission_event_daily_bonuses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_id', 255)->comment('mst_mission_event_daily_bonuses.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_event_daily_bonus_id']);
        });

        Schema::dropIfExists('usr_mission_event_daily_progresses');
        Schema::create('usr_mission_event_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('生涯累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_mission_event_progresses');
        Schema::create('usr_mission_event_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_mission_events');
        Schema::create('usr_mission_events', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_id', 255)->comment('mst_mission_events.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
            $table->primary(['usr_user_id', 'mst_mission_event_id']);
        });

        Schema::dropIfExists('usr_mission_recent_additions');
        Schema::create('usr_mission_recent_additions', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mission_type', 255)->comment('ミッションタイプ');
            $table->integer('latest_release_key')->comment('判定済みの中で最新のリリースキー');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mission_type']);
        });

        Schema::dropIfExists('usr_mission_statuses');
        Schema::create('usr_mission_statuses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->unsignedSmallInteger('beginner_mission_status')->default(0)->comment('初心者ミッション未クリア: 0 初心者ミッションクリア: 1');
            $table->string('latest_mst_hash', 255)->comment('前回即時判定をしたときのマスタデータハッシュ値');
            $table->timestampTz('mission_unlocked_at')->nullable()->comment('ミッション解放日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_mission_weeklies');
        Schema::create('usr_mission_weeklies', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_weekly_id', 255)->comment('mst_mission_weeklies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_weekly_id']);
        });

        Schema::dropIfExists('usr_mission_weekly_progresses');
        Schema::create('usr_mission_weekly_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('ウィークリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::dropIfExists('usr_outpost_enhancements');
        Schema::create('usr_outpost_enhancements', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_outpost_id', 255);
            $table->string('mst_outpost_enhancement_id', 255);
            $table->unsignedInteger('level');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_outpost_enhancement_id']);
        });

        Schema::dropIfExists('usr_outposts');
        Schema::create('usr_outposts', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_outpost_id', 255);
            $table->string('mst_artwork_id', 255)->nullable()->comment('mst_artworks.id');
            $table->tinyInteger('is_used')->default(0)->comment('使用中かどうか');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_outpost_id']);
        });

        Schema::dropIfExists('usr_parties');
        Schema::create('usr_parties', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->unsignedInteger('party_no')->comment('パーティ番号');
            $table->string('party_name', 10)->comment('パーティ名');
            $table->string('usr_unit_id_1', 255)->comment('1スロット目のユーザーユニットID');
            $table->string('usr_unit_id_2', 255)->nullable()->comment('2スロット目のユーザーユニットID');
            $table->string('usr_unit_id_3', 255)->nullable()->comment('3スロット目のユーザーユニットID');
            $table->string('usr_unit_id_4', 255)->nullable()->comment('4スロット目のユーザーユニットID');
            $table->string('usr_unit_id_5', 255)->nullable()->comment('5スロット目のユーザーユニットID');
            $table->string('usr_unit_id_6', 255)->nullable()->comment('6スロット目のユーザーユニットID');
            $table->string('usr_unit_id_7', 255)->nullable()->comment('7スロット目のユーザーユニットID');
            $table->string('usr_unit_id_8', 255)->nullable()->comment('8スロット目のユーザーユニットID');
            $table->string('usr_unit_id_9', 255)->nullable()->comment('9スロット目のユーザーユニットID');
            $table->string('usr_unit_id_10', 255)->nullable()->comment('10スロット目のユーザーユニットID');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'party_no']);
        });

        Schema::dropIfExists('usr_received_unit_encyclopedia_rewards');
        Schema::create('usr_received_unit_encyclopedia_rewards', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_unit_encyclopedia_reward_id', 255)->comment('mst_unit_encyclopedia_rewards.id');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_unit_encyclopedia_reward_id']);
        });

        Schema::dropIfExists('usr_shop_items');
        Schema::create('usr_shop_items', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_usersのid');
            $table->string('mst_shop_item_id', 255)->comment('mst_shop_itemsのid');
            $table->unsignedInteger('trade_count')->comment('交換回数');
            $table->unsignedInteger('trade_total_count')->default(0)->comment('累計交換回数');
            $table->timestampTz('last_reset_at')->comment('最終リセット日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_shop_item_id']);
        });

        Schema::dropIfExists('usr_shop_passes');
        Schema::create('usr_shop_passes', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_shop_pass_id', 255);
            $table->unsignedBigInteger('daily_reward_received_count')->default(0)->comment('毎日報酬を受け取った回数');
            $table->timestampTz('daily_latest_received_at')->nullable()->comment('毎日報酬を受け取った日時');
            $table->timestampTz('start_at')->comment('パスの開始日時');
            $table->timestampTz('end_at')->comment('パスの終了日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_shop_pass_id']);
        });

        Schema::dropIfExists('usr_stage_events');
        Schema::create('usr_stage_events', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_stage_id', 255);
            $table->unsignedBigInteger('clear_count')->default(0)->comment('クリア回数');
            $table->unsignedBigInteger('reset_clear_count')->default(0)->comment('リセットからのクリア回数');
            $table->unsignedBigInteger('reset_ad_challenge_count')->default(0)->comment('リセットからの広告視聴での挑戦回数');
            $table->unsignedInteger('reset_clear_time_ms')->nullable()->comment('開催期間中のクリアタイム(ミリ秒)');
            $table->unsignedInteger('clear_time_ms')->nullable()->comment('クリアタイム(ミリ秒)');
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時');
            $table->timestampTz('latest_event_setting_end_at')->default('2000-01-01 00:00:00')->comment('mst_stage_event_settings.end_at');
            $table->timestampTz('last_challenged_at')->nullable()->comment('最終挑戦日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_stage_id']);
        });

        Schema::dropIfExists('usr_stage_sessions');
        Schema::create('usr_stage_sessions', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_stage_id', 255);
            $table->unsignedTinyInteger('is_valid')->default(0);
            $table->unsignedInteger('party_no')->default(0)->comment('パーティ番号');
            $table->unsignedInteger('continue_count')->default(0)->comment('コンティニュー回数');
            $table->unsignedInteger('daily_continue_ad_count')->default(0)->comment('1日の広告コンティニュー回数');
            $table->json('opr_campaign_ids')->nullable()->comment('opr_campaigns.idの配列');
            $table->timestampTz('latest_reset_at')->comment('リセット日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_stages');
        Schema::create('usr_stages', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('mst_stage_id', 255);
            $table->tinyInteger('clear_status');
            $table->bigInteger('clear_count');
            $table->unsignedInteger('clear_time_ms')->nullable()->comment('クリアタイム(ミリ秒)');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_stage_id']);
        });

        Schema::dropIfExists('usr_store_products');
        Schema::create('usr_store_products', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_usersのid');
            $table->string('product_sub_id', 255)->comment('opr_productsのid');
            $table->unsignedInteger('purchase_count')->comment('購入回数');
            $table->unsignedInteger('purchase_total_count')->default(0)->comment('累計購入回数');
            $table->timestampTz('last_reset_at')->comment('最終リセット日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'product_sub_id']);
        });

        Schema::dropIfExists('usr_temporary_individual_messages');
        Schema::create('usr_temporary_individual_messages', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('opr_message_id', 255);
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'opr_message_id']);
        });

        Schema::dropIfExists('usr_user_buy_counts');
        Schema::create('usr_user_buy_counts', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->integer('daily_buy_stamina_ad_count')->default(0);
            $table->timestampTz('daily_buy_stamina_ad_at')->nullable();
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_user_logins');
        Schema::create('usr_user_logins', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->timestampTz('first_login_at')->comment('初回ログイン日時');
            $table->timestampTz('last_login_at')->comment('最終ログイン日時');
            $table->timestampTz('hourly_accessed_at')->comment('1時間毎の最初のアクセス日時');
            $table->unsignedInteger('login_count')->default(0)->comment('ログイン回数');
            $table->integer('login_day_count')->default(0)->comment('生涯累計のログイン日数');
            $table->integer('login_continue_day_count')->default(0)->comment('連続ログイン日数。連続ログインが途切れたら0にリセットする。');
            $table->integer('comeback_day_count')->default(0)->comment('最終ログインからの復帰にかかった日数');
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_user_parameters');
        Schema::create('usr_user_parameters', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->unsignedInteger('level')->default(1);
            $table->bigInteger('exp')->default(0)->comment('');
            $table->unsignedBigInteger('coin')->default(0);
            $table->unsignedInteger('stamina')->default(0);
            $table->timestampTz('stamina_updated_at')->nullable();
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });

        Schema::dropIfExists('usr_user_profiles');
        Schema::create('usr_user_profiles', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255);
            $table->string('my_id', 255)->unique();
            $table->string('name', 32)->default('');
            $table->unsignedTinyInteger('is_change_name')->default(0);
            $table->text('birth_date')->comment('暗号化された生年月日の数字データ');
            $table->string('mst_unit_id', 255)->comment('アバターとして設定したユニットのID');
            $table->string('mst_emblem_id', 255)->default('')->comment('エンブレムID');
            $table->string('mst_avatar_id', 255)->default('0');
            $table->string('mst_avatar_frame_id', 255)->default('0');
            $table->timestampTz('name_update_at')->nullable();
            $table->timestampsTz();

            $table->primary(['usr_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_artwork_fragments');
        Schema::create('usr_artwork_fragments', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_artwork_id', 255)->comment('mst_artworks.id');
            $table->string('mst_artwork_fragment_id', 255)->comment('mst_artwork_fragments.id');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_artwork_fragment_id'], 'uk_usr_user_id_mst_artwork_fragment_id');
            $table->index(['usr_user_id', 'mst_artwork_id']);
        });

        Schema::dropIfExists('usr_artworks');
        Schema::create('usr_artworks', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_artwork_id', 255)->comment('mst_artworks.id');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_artwork_id'], 'uk_usr_user_id_mst_artwork_id');
        });

        Schema::dropIfExists('usr_cheat_sessions');
        Schema::create('usr_cheat_sessions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('content_type', 255)->comment('コンテンツのタイプ');
            $table->string('target_id', 255)->nullable()->comment('降臨バトルの場合はmst_advent_battles.id');
            $table->json('party_status')->nullable()->comment('パーティステータス');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_condition_packs');
        Schema::create('usr_condition_packs', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_pack_id', 255)->comment('mst_packs.id');
            $table->timestampTz('start_date')->comment('購入可能期間開始日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_pack_id']);
        });

        // 未使用
        Schema::dropIfExists('usr_device_link_passwords');
        Schema::create('usr_device_link_passwords', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique();
            $table->string('auth_id', 256)->unique();
            $table->string('auth_password', 256);
            $table->timestampsTz();
        });

        // 未使用
        Schema::dropIfExists('usr_device_link_socials');
        Schema::create('usr_device_link_socials', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->tinyInteger('auth_type');
            $table->string('auth_token', 256);
            $table->timestampsTz();

            $table->unique(['auth_type', 'auth_token'], 'auth_type_auth_token');
            $table->unique(['usr_user_id', 'auth_type'], 'uk_usr_user_id_auth_type');
        });

        //usr_devices
        // usr_user_idで検索していないのでスキップ

        Schema::dropIfExists('usr_emblems');
        Schema::create('usr_emblems', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_emblem_id', 255);
            $table->timestampsTz();

            $table->index(['usr_user_id', 'mst_emblem_id'], 'usr_user_id_mst_emblem_id_index');
        });

        Schema::dropIfExists('usr_gacha_uppers');
        Schema::create('usr_gacha_uppers', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('ユーザーID');
            $table->string('upper_group', 255)->comment('天井設定区分');
            $table->string('upper_type', 255)->default('')->comment('天井タイプ');
            $table->unsignedInteger('count')->default(0)->comment('天井を保証する回数 リセット条件に合致した場合カウントは0に戻る');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'upper_group', 'upper_type'], 'usr_user_id_upper_group_upper_type_unique');
        });

        Schema::dropIfExists('usr_gachas');
        Schema::create('usr_gachas', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index();
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->timestampTz('ad_played_at')->nullable()->default(null)->comment('広告で回した時間');
            $table->timestampTz('played_at')->nullable()->default(null)->comment('回した時間');
            $table->unsignedInteger('ad_count')->default(0)->comment('広告でガチャを回した回数');
            $table->unsignedInteger('ad_daily_count')->default(0)->comment('広告で本日ガチャを回した回数');
            $table->unsignedInteger('count')->default(0)->comment('ガチャを回した回数');
            $table->unsignedInteger('daily_count')->default(0)->comment('日次でガチャを回した回数');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'opr_gacha_id'], 'usr_user_id_opr_gacha_id_unique');
        });

        Schema::dropIfExists('usr_idle_incentives');
        Schema::create('usr_idle_incentives', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique()->comment('usr_users.id');
            $table->unsignedInteger('diamond_quick_receive_count')->default(0);
            $table->unsignedInteger('ad_quick_receive_count')->default(0)->comment('広告でのクイック獲得回数');
            $table->timestampTz('idle_started_at')->comment('放置開始時間');
            $table->timestampTz('diamond_quick_receive_at')->default('2000-01-01 00:00:00');
            $table->timestampTz('ad_quick_receive_at')->comment('広告でクイック獲得を実行した時刻');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_items');
        Schema::create('usr_items', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_item_id', 255)->default('0');
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_item_id'], 'uk_mst_item_id');
        });

        Schema::dropIfExists('usr_mission_achievement_progresses');
        Schema::create('usr_mission_achievement_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー「criterion_type:criterion_value」');
            $table->unsignedBigInteger('progress')->comment('生涯累積進捗値');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'uk_usr_user_id_criterion_key');
        });

        Schema::dropIfExists('usr_mission_achievements');
        Schema::create('usr_mission_achievements', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_achievement_id', 255)->comment('mst_mission_achievements.id');
            $table->unsignedTinyInteger('status')->comment('0:未クリア, 1:クリア, 2:報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_achievement_id'], 'uk_usr_user_id_mst_mission_achievement_id');
        });

        Schema::dropIfExists('usr_mission_beginner_progresses');
        Schema::create('usr_mission_beginner_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('初心者ミッション累積進捗値');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'uk_usr_user_id_criterion_key');
        });

        Schema::dropIfExists('usr_mission_beginners');
        Schema::create('usr_mission_beginners', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_beginner_id', 255)->comment('mst_mission_beginners.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_beginner_id'], 'uk_usr_user_id_mst_mission_beginner_id');
        });

        Schema::dropIfExists('usr_mission_dailies');
        Schema::create('usr_mission_dailies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_daily_id', 255)->comment('mst_mission_dailies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_daily_id'], 'uk_usr_user_id_mst_mission_daily_id');
        });

        Schema::dropIfExists('usr_mission_daily_bonuses');
        Schema::create('usr_mission_daily_bonuses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_daily_bonus_id', 255)->comment('mst_mission_daily_bonuses.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_daily_bonus_id'], 'uk_usr_user_id_mst_mission_daily_bonus_id');
        });

        Schema::dropIfExists('usr_mission_daily_progresses');
        Schema::create('usr_mission_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'uk_usr_user_id_criterion_key');
        });

        Schema::dropIfExists('usr_mission_event_dailies');
        Schema::create('usr_mission_event_dailies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_daily_id', 255)->comment('mst_mission_event_dailies.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
            $table->unique(['usr_user_id', 'mst_mission_event_daily_id'], 'usr_user_id_mst_mission_event_daily_id_unique');
        });

        Schema::dropIfExists('usr_mission_event_daily_bonus_progresses');
        Schema::create('usr_mission_event_daily_bonus_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_schedule_id', 255)->comment('mst_mission_event_daily_bonus_schedules.id');
            $table->unsignedInteger('progress')->comment('ログイン回数進捗');
            $table->timestampTz('latest_update_at')->nullable()->comment('ログイン更新日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_mission_event_daily_bonus_schedule_id'], 'uk_usr_user_id_event_daily_bonus_schedule_id');
        });

        Schema::dropIfExists('usr_mission_event_daily_bonuses');
        Schema::create('usr_mission_event_daily_bonuses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_event_daily_bonus_id', 255)->comment('mst_mission_event_daily_bonuses.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_event_daily_bonus_id'], 'uk_usr_user_id_mst_mission_event_daily_bonus_id');
        });

        Schema::dropIfExists('usr_mission_event_daily_progresses');
        Schema::create('usr_mission_event_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('生涯累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'usr_user_id_criterion_key_unique');
        });

        Schema::dropIfExists('usr_mission_event_progresses');
        Schema::create('usr_mission_event_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'usr_user_id_criterion_key_unique');
        });

        Schema::dropIfExists('usr_mission_events');
        Schema::create('usr_mission_events', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_id', 255)->comment('mst_mission_events.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
            $table->unique(['usr_user_id', 'mst_mission_event_id'], 'usr_user_id_mst_mission_event_id_unique');
        });

        Schema::dropIfExists('usr_mission_recent_additions');
        Schema::create('usr_mission_recent_additions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mission_type', 255)->comment('ミッションタイプ');
            $table->integer('latest_release_key')->comment('判定済みの中で最新のリリースキー');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mission_type'], 'uk_usr_user_id_mission_type');
        });

        Schema::dropIfExists('usr_mission_statuses');
        Schema::create('usr_mission_statuses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique()->comment('usr_users.id');
            $table->unsignedSmallInteger('beginner_mission_status')->default(0)->comment('初心者ミッション未クリア: 0 初心者ミッションクリア: 1');
            $table->string('latest_mst_hash', 255)->comment('前回即時判定をしたときのマスタデータハッシュ値');
            $table->timestampTz('mission_unlocked_at')->nullable()->comment('ミッション解放日時');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_mission_weeklies');
        Schema::create('usr_mission_weeklies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_weekly_id', 255)->comment('mst_mission_weeklies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_weekly_id'], 'uk_usr_user_id_mst_mission_weekly_id');
        });

        Schema::dropIfExists('usr_mission_weekly_progresses');
        Schema::create('usr_mission_weekly_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('ウィークリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'criterion_key'], 'uk_usr_user_id_criterion_key');
        });

        Schema::dropIfExists('usr_outpost_enhancements');
        Schema::create('usr_outpost_enhancements', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index();
            $table->string('mst_outpost_id', 255);
            $table->string('mst_outpost_enhancement_id', 255);
            $table->unsignedInteger('level');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_outpost_id', 'mst_outpost_enhancement_id'], 'usr_outpost_enhancements_unique');
        });

        Schema::dropIfExists('usr_outposts');
        Schema::create('usr_outposts', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index();
            $table->string('mst_outpost_id', 255);
            $table->string('mst_artwork_id', 255)->nullable()->comment('mst_artworks.id');
            $table->tinyInteger('is_used')->default(0)->comment('使用中かどうか');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_outpost_id'], 'usr_outposts_unique');
        });

        Schema::dropIfExists('usr_parties');
        Schema::create('usr_parties', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->unsignedInteger('party_no')->comment('パーティ番号');
            $table->string('party_name', 10)->comment('パーティ名');
            $table->string('usr_unit_id_1', 255)->comment('1スロット目のユーザーユニットID');
            $table->string('usr_unit_id_2', 255)->nullable()->comment('2スロット目のユーザーユニットID');
            $table->string('usr_unit_id_3', 255)->nullable()->comment('3スロット目のユーザーユニットID');
            $table->string('usr_unit_id_4', 255)->nullable()->comment('4スロット目のユーザーユニットID');
            $table->string('usr_unit_id_5', 255)->nullable()->comment('5スロット目のユーザーユニットID');
            $table->string('usr_unit_id_6', 255)->nullable()->comment('6スロット目のユーザーユニットID');
            $table->string('usr_unit_id_7', 255)->nullable()->comment('7スロット目のユーザーユニットID');
            $table->string('usr_unit_id_8', 255)->nullable()->comment('8スロット目のユーザーユニットID');
            $table->string('usr_unit_id_9', 255)->nullable()->comment('9スロット目のユーザーユニットID');
            $table->string('usr_unit_id_10', 255)->nullable()->comment('10スロット目のユーザーユニットID');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_received_unit_encyclopedia_rewards');
        Schema::create('usr_received_unit_encyclopedia_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_unit_encyclopedia_reward_id', 255)->comment('mst_unit_encyclopedia_rewards.id');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_unit_encyclopedia_reward_id'], 'uk_usr_user_id_mst_unit_encyclopedia_reward_id');
        });

        Schema::dropIfExists('usr_shop_items');
        Schema::create('usr_shop_items', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_usersのid');
            $table->string('mst_shop_item_id', 255)->comment('mst_shop_itemsのid');
            $table->unsignedInteger('trade_count')->comment('交換回数');
            $table->unsignedInteger('trade_total_count')->default(0)->comment('累計交換回数');
            $table->timestampTz('last_reset_at')->comment('最終リセット日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_shop_item_id']);
        });

        Schema::dropIfExists('usr_shop_passes');
        Schema::create('usr_shop_passes', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_shop_pass_id', 255);
            $table->unsignedBigInteger('daily_reward_received_count')->default(0)->comment('毎日報酬を受け取った回数');
            $table->timestampTz('daily_latest_received_at')->nullable()->comment('毎日報酬を受け取った日時');
            $table->timestampTz('start_at')->comment('パスの開始日時');
            $table->timestampTz('end_at')->comment('パスの終了日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_shop_pass_id'], 'usr_user_id_mst_shop_pass_id_unique');
        });

        Schema::dropIfExists('usr_stage_events');
        Schema::create('usr_stage_events', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_stage_id', 255);
            $table->unsignedBigInteger('clear_count')->default(0)->comment('クリア回数');
            $table->unsignedBigInteger('reset_clear_count')->default(0)->comment('リセットからのクリア回数');
            $table->unsignedBigInteger('reset_ad_challenge_count')->default(0)->comment('リセットからの広告視聴での挑戦回数');
            $table->unsignedInteger('reset_clear_time_ms')->nullable()->comment('開催期間中のクリアタイム(ミリ秒)');
            $table->unsignedInteger('clear_time_ms')->nullable()->comment('クリアタイム(ミリ秒)');
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時');
            $table->timestampTz('latest_event_setting_end_at')->default('2000-01-01 00:00:00')->comment('mst_stage_event_settings.end_at');
            $table->timestampTz('last_challenged_at')->nullable()->comment('最終挑戦日時');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_stage_id'], 'uk_usr_user_id_mst_stage_id');
        });

        Schema::dropIfExists('usr_stage_sessions');
        Schema::create('usr_stage_sessions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique();
            $table->string('mst_stage_id', 255);
            $table->unsignedTinyInteger('is_valid')->default(0);
            $table->unsignedInteger('party_no')->default(0)->comment('パーティ番号');
            $table->unsignedInteger('continue_count')->default(0)->comment('コンティニュー回数');
            $table->unsignedInteger('daily_continue_ad_count')->default(0)->comment('1日の広告コンティニュー回数');
            $table->json('opr_campaign_ids')->nullable()->comment('opr_campaigns.idの配列');
            $table->timestampTz('latest_reset_at')->comment('リセット日時');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_stages');
        Schema::create('usr_stages', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('mst_stage_id', 255);
            $table->tinyInteger('clear_status');
            $table->bigInteger('clear_count');
            $table->unsignedInteger('clear_time_ms')->nullable()->comment('クリアタイム(ミリ秒)');
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'mst_stage_id'], 'usr_stages_usr_user_id_mst_stage_id_unique');
        });

        Schema::dropIfExists('usr_store_products');
        Schema::create('usr_store_products', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_usersのid');
            $table->string('product_sub_id', 255)->comment('opr_productsのid');
            $table->unsignedInteger('purchase_count')->comment('購入回数');
            $table->unsignedInteger('purchase_total_count')->default(0)->comment('累計購入回数');
            $table->timestampTz('last_reset_at')->comment('最終リセット日時');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_temporary_individual_messages');
        Schema::create('usr_temporary_individual_messages', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('opr_message_id', 255);
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'opr_message_id'], 'usr_user_id_opr_message_id_unique');
        });

        Schema::dropIfExists('usr_user_buy_counts');
        Schema::create('usr_user_buy_counts', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique();
            $table->integer('daily_buy_stamina_ad_count')->default(0);
            $table->timestampTz('daily_buy_stamina_ad_at')->nullable();
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_user_logins');
        Schema::create('usr_user_logins', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique()->comment('usr_users.id');
            $table->timestampTz('first_login_at')->comment('初回ログイン日時');
            $table->timestampTz('last_login_at')->comment('最終ログイン日時');
            $table->timestampTz('hourly_accessed_at')->comment('1時間毎の最初のアクセス日時');
            $table->unsignedInteger('login_count')->default(0)->comment('ログイン回数');
            $table->integer('login_day_count')->default(0)->comment('生涯累計のログイン日数');
            $table->integer('login_continue_day_count')->default(0)->comment('連続ログイン日数。連続ログインが途切れたら0にリセットする。');
            $table->integer('comeback_day_count')->default(0)->comment('最終ログインからの復帰にかかった日数');
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_user_parameters');
        Schema::create('usr_user_parameters', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique();
            $table->unsignedInteger('level')->default(1);
            $table->bigInteger('exp')->default(0)->comment('');
            $table->unsignedBigInteger('coin')->default(0);
            $table->unsignedInteger('stamina')->default(0);
            $table->timestampTz('stamina_updated_at')->nullable();
            $table->timestampsTz();
        });

        Schema::dropIfExists('usr_user_profiles');
        Schema::create('usr_user_profiles', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->unique();
            $table->string('my_id', 255)->unique();
            $table->string('name', 32)->default('');
            $table->unsignedTinyInteger('is_change_name')->default(0);
            $table->text('birth_date')->comment('暗号化された生年月日の数字データ');
            $table->string('mst_unit_id', 255)->comment('アバターとして設定したユニットのID');
            $table->string('mst_emblem_id', 255)->default('')->comment('エンブレムID');
            $table->string('mst_avatar_id', 255)->default('0');
            $table->string('mst_avatar_frame_id', 255)->default('0');
            $table->timestampTz('name_update_at')->nullable();
            $table->timestampsTz();
        });
    }
};
