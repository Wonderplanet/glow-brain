<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 一次通貨返却実行を実行するための管理テーブル
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 一次通貨返却を実行するタスクの管理テーブル
        Schema::create('adm_bulk_currency_revert_tasks', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_user_id', 255)->comment('実行した管理ツールのユーザID');
            $table->string('file_name', 512)->comment('ファイル名');
            $table->bigInteger('revert_currency_num')->unsigned()->comment('返却する一次通貨数');
            $table->text('comment')->comment('コメント');
            // ステータス系は後の改修で変更される可能性があるためstringにしている
            $table->string('status', 255)->comment('タスクの状態');
            $table->bigInteger('total_count')->unsigned()->comment('全体の件数');
            $table->bigInteger('success_count')->unsigned()->comment('成功した件数');
            $table->bigInteger('error_count')->unsigned()->comment('エラーが発生した件数');
            $table->text('error_message')->nullable()->comment('エラーメッセージ');

            $table->timestampsTz();

            // index作成
            $table->index('adm_user_id', 'user_id_index');
            $table->index('created_at', 'created_at_index');
            $table->index('status', 'status_index');
            $table->index('file_name', 'file_name_index');

            $table->comment('一次通貨返却を実行するタスクの管理テーブル');
        });

        // 一次通貨返却タスクの個別実行状況を管理するテーブル
        // 配布個数やコメントを変更して配布する場合は新しいadm_bulk_currency_revert_tasksに紐づいてデータを入れ直す想定
        Schema::create('adm_bulk_currency_revert_task_targets', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_bulk_currency_revert_task_id', 255)->comment('返却タスクのID');
            $table->bigInteger('seq_no')->unsigned()->comment('登録番号');
            $table->string('usr_user_id', 255)->comment('返却対象ユーザーID');
            // ステータス系は後の改修で変更される可能性があるためstringにしている
            $table->string('status', 255)->comment('個別タスクの状態');
            $table->bigInteger('revert_currency_num')->unsigned()->comment('返却する一次通貨数');
            $table->timestampTz('consumed_at')->comment('コンテンツ消費日時');
            $table->string('trigger_type', 255)->comment('消費コンテンツタイプ');
            $table->string('trigger_id', 255)->comment('消費コンテンツID');
            $table->string('trigger_name', 255)->comment('消費コンテンツ名');
            $table->string('request_id', 255)->comment('リクエストID');
            $table->bigInteger('sum_log_change_amount_paid')->comment('消費有償一次通貨数(合計)');
            $table->bigInteger('sum_log_change_amount_free')->comment('消費無償一次通貨数(合計)');
            // 対応するadm_bulk_currency_revert_tasks.commentと同じものが入る
            // リレーションとしては対応するadm_bulk_currency_revert_tasksを参照したほうが正しいが、
            // このレコードのみで配布処理情報を完結させたいため、こちらにも格納している
            $table->text('comment')->comment('コメント');
            $table->text('error_message')->nullable()->comment('エラーメッセージ');

            $table->timestampsTz();

            // index作成
            $table->index('adm_bulk_currency_revert_task_id', 'adm_bulk_currency_revert_task_id_index');
            $table->index('usr_user_id', 'usr_user_id_index');
            $table->index('status', 'status_index');
            $table->index('created_at', 'created_at_index');
            $table->index(['trigger_type', 'trigger_id'], 'trigger_type_trigger_id_index');
            $table->index('trigger_name', 'trigger_name_index');
            $table->unique(['adm_bulk_currency_revert_task_id', 'seq_no'], 'adm_bulk_currency_revert_task_id_seq_no_unique');

            $table->comment('一次通貨返却タスクの個別実行状況を管理するテーブル');
        });

        // 対象となるlog_currency_paid_idのテーブル
        // idのリストになるため念の為上限を設けないようにしておきたいのと、
        // 対象ログレコードと結合する場合を考えて別テーブルにしました
        Schema::create('adm_bulk_currency_revert_task_target_paid_logs', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_bulk_currency_revert_task_target_id', 255)->comment('一次通貨返却タスクの個別実行状況テーブルID');
            $table->string('usr_user_id', 255)->comment('ユーザーID');
            $table->string('log_currency_paid_id', 255)->comment('有償一次通貨の消費ログID');

            $table->timestampsTz();

            // index作成
            $table->index('adm_bulk_currency_revert_task_target_id', 'adm_bulk_currency_revert_task_target_id_index');
            $table->index('usr_user_id', 'usr_user_id_index');
            $table->index('log_currency_paid_id', 'log_currency_paid_id_index');

            $table->comment('一次通貨返却タスクの個別実行対象となるlog_currency_paid_idのテーブル');
        });

        // 対象となるlog_currency_free_idのテーブル
        Schema::create('adm_bulk_currency_revert_task_target_free_logs', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_bulk_currency_revert_task_target_id', 255)->comment('一次通貨返却タスクの個別実行状況テーブルID');
            $table->string('usr_user_id', 255)->comment('ユーザーID');
            $table->string('log_currency_free_id', 255)->comment('無償一次通貨の消費ログID');

            $table->timestampsTz();

            // index作成
            $table->index('adm_bulk_currency_revert_task_target_id', 'adm_bulk_currency_revert_task_target_id_index');
            $table->index('usr_user_id', 'usr_user_id_index');
            $table->index('log_currency_free_id', 'log_currency_free_id_index');

            $table->comment('一次通貨返却タスクの個別実行対象となるlog_currency_free_idのテーブル');
        });

        // 返却を実施した後のlog_currency_revert_history_idのテーブル
        Schema::create('adm_bulk_currency_revert_task_target_revert_history_logs', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('adm_bulk_currency_revert_task_target_id', 255)->comment('一次通貨返却タスクの個別実行状況テーブルID');
            $table->string('usr_user_id', 255)->comment('ユーザーID');
            $table->string('log_currency_revert_history_id', 255)->comment('返却を実行した一次通貨返却ログID');

            $table->timestampsTz();

            // index作成
            $table->index('adm_bulk_currency_revert_task_target_id', 'adm_bulk_currency_revert_task_target_id_index');
            $table->index('usr_user_id', 'usr_user_id_index');
            $table->index('log_currency_revert_history_id', 'log_currency_revert_history_id_index');

            $table->comment('一次通貨返却タスクの個別実行対象となるlog_currency_revert_history_idのテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_bulk_currency_revert_tasks');
        Schema::dropIfExists('adm_bulk_currency_revert_task_targets');
        Schema::dropIfExists('adm_bulk_currency_revert_task_target_paid_logs');
        Schema::dropIfExists('adm_bulk_currency_revert_task_target_free_logs');
        Schema::dropIfExists('adm_bulk_currency_revert_task_target_revert_history_logs');
    }
};
