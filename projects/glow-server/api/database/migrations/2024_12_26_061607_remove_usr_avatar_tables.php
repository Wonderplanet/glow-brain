<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CREATE TABLE `usr_avatars` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_avatar_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     KEY `usr_avatars_usr_user_id_index` (`usr_user_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `usr_avatar_frames` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_avatar_frame_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     KEY `usr_avatar_frames_usr_user_id_index` (`usr_user_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('usr_avatars');
        Schema::dropIfExists('usr_avatar_frames');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('usr_avatars', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index();
            $table->string('mst_avatar_id', 255);
            $table->timestamps();
        });

        Schema::create('usr_avatar_frames', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index();
            $table->string('mst_avatar_frame_id', 255);
            $table->timestamps();
        });
    }
};
