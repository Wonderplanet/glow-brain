# glow-brain-gemini 全ソースコード (Part 3)

生成日時: 2026-01-16 14:58:25

---

<!-- FILE: ./projects/glow-server/api/database/schema/exports/master_tables_schema.json -->
## ./projects/glow-server/api/database/schema/exports/master_tables_schema.json

```json
{
    "databases": {
        "mst": {
            "tables": {
                "mst_advent_battles": {
                    "comment": "降臨バトルの基本設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_events.id"
                        },
                        "mst_in_game_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_in_games.id"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "アセットキー"
                        },
                        "advent_battle_type": {
                            "type": "enum('ScoreChallenge','Raid')",
                            "nullable": false,
                            "comment": "降臨バトルタイプ"
                        },
                        "initial_battle_point": {
                            "type": "int",
                            "nullable": false,
                            "default": "0",
                            "comment": "インゲーム開始時のリーダーP"
                        },
                        "mst_stage_rule_group_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "mst_stage_event_rules.group_id"
                        },
                        "event_bonus_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_event_bonus_units.event_bonus_group_id"
                        },
                        "challengeable_count": {
                            "type": "smallint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "1日の挑戦可能回数"
                        },
                        "ad_challengeable_count": {
                            "type": "smallint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "1日の広告視聴での挑戦可能回数"
                        },
                        "display_mst_unit_id1": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "降臨バトルトップ場所1に表示するキャラ"
                        },
                        "display_mst_unit_id2": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "降臨バトルトップ場所2に表示するキャラ"
                        },
                        "display_mst_unit_id3": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "降臨バトルトップ場所3に表示するキャラ"
                        },
                        "exp": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "獲得リーダーEXP"
                        },
                        "coin": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "獲得コイン"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "降臨バトル開始日"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "降臨バトル終了日"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "score_addition_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "AllEnemiesAndOutPost",
                            "comment": "降臨バトルスコア加算タイプ(スコアチャレンジ、レイド)"
                        },
                        "score_additional_coef": {
                            "type": "decimal(5,3)",
                            "nullable": false,
                            "default": "0.000",
                            "comment": "降臨バトルスコア加算係数"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_advent_battles_i18n": {
                    "comment": "降臨バトルの基本設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_advent_battle_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_advent_battles.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "名前"
                        },
                        "boss_description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "降臨バトルボス説明文"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_artworks": {
                    "comment": "原画の設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_series.id"
                        },
                        "outpost_additional_hp": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "完成時にゲートに加算するHP"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "原画画像アセット"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "ソート順"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_artworks_i18n": {
                    "comment": "原画名などの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_artwork_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_series.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(40)",
                            "nullable": false,
                            "comment": "原画名"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "原画の説明文"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_mst_artwork_id_language": {
                            "type": "unique",
                            "columns": [
                                "mst_artwork_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_comeback_bonuses": {
                    "comment": "カムバックボーナスの設定(現在未対応、対応予定あり)",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_comeback_bonus_schedule_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_comeback_bonus_schedules.id"
                        },
                        "login_day_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "条件とするログイン日数"
                        },
                        "mst_daily_bonus_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_daily_bonus_reward.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "表示順"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_schedule_id_login_day_count": {
                            "type": "unique",
                            "columns": [
                                "mst_comeback_bonus_schedule_id",
                                "login_day_count"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_comeback_bonus_schedules": {
                    "comment": "カムバックボーナスのスケジュール設定(現在未対応、対応予定あり)",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "inactive_condition_days": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "未ログイン期間の条件日数"
                        },
                        "duration_days": {
                            "type": "int",
                            "nullable": false,
                            "comment": "有効日数"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日時"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_configs": {
                    "comment": "定数設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "キー"
                        },
                        "value": {
                            "type": "text",
                            "nullable": false,
                            "comment": "設定値"
                        }
                    },
                    "indexes": {
                        "mst_configs_key_unique": {
                            "type": "unique",
                            "columns": [
                                "key"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_daily_bonus_rewards": {
                    "comment": "ログインボーナスの設定用テーブル(ただ、現在は通常ログインボーナスもイベントログインボーナスもmst_mission_rewardsで設定されている)(現在は未使用)",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "報酬グルーピングID"
                        },
                        "resource_type": {
                            "type": "enum('Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit')",
                            "nullable": false,
                            "comment": "報酬タイプ"
                        },
                        "resource_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "報酬ID"
                        },
                        "resource_amount": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "報酬数量"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "idx_group_id": {
                            "type": "index",
                            "columns": [
                                "group_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_emblems": {
                    "comment": "エンブレム設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "emblem_type": {
                            "type": "enum('Event','Series')",
                            "nullable": false,
                            "comment": "エンブレムのタイプ"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "作品ID"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "経緯情報ソース"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_emblems_i18n": {
                    "comment": "エンブレム設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_emblem_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_emblems.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "エンブレムの名称"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "フレーバーテキスト"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "language_index": {
                            "type": "index",
                            "columns": [
                                "language"
                            ],
                            "index_type": "BTREE"
                        },
                        "mst_emblem_id_index": {
                            "type": "index",
                            "columns": [
                                "mst_emblem_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_enemy_characters": {
                    "comment": "敵ユニットの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "作品ID"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "経緯情報ソース"
                        },
                        "is_phantomized": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "プレイアブルキャラの敵化専用表現用"
                        },
                        "is_displayed_encyclopedia": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "図鑑に表示するかのフラグ"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_enemy_characters_i18n": {
                    "comment": "敵ユニットの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_enemy_character_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ファントムID"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ファントム名"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ファントム説明"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_events": {
                    "comment": "イベント設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "作品ID"
                        },
                        "is_displayed_series_logo": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "作品ロゴの表示有無"
                        },
                        "is_displayed_jump_plus": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "作品を読むボタンの表示有無"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日時"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アセットキー"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "mst_series_id_index": {
                            "type": "index",
                            "columns": [
                                "mst_series_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_events_i18n": {
                    "comment": "イベント設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "リレーション向けMstEventId"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語設定"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "イベント名"
                        },
                        "balloon": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "吹き出し内テキスト"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_mst_event_id_language": {
                            "type": "unique",
                            "columns": [
                                "mst_event_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_items": {
                    "comment": "アイテム設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "type": {
                            "type": "enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal','StaminaRecoveryPercent','StaminaRecoveryFixed')",
                            "nullable": true
                        },
                        "group_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アプリの表示タブ用"
                        },
                        "rarity": {
                            "type": "enum('N','R','SR','SSR','UR')",
                            "nullable": false,
                            "comment": "レア度"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アセットキー"
                        },
                        "effect_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "特定item_typeのときの効果値"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_series.id"
                        },
                        "sort_order": {
                            "type": "int",
                            "nullable": false,
                            "default": "0",
                            "comment": "表示順番"
                        },
                        "start_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日"
                        },
                        "end_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "destination_opr_product_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "画面遷移先条件向けOprProductId"
                        }
                    },
                    "indexes": {
                        "mst_items_item_type_index": {
                            "type": "index",
                            "columns": [
                                "type"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_items_i18n": {
                    "comment": "アイテム設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_item_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アイテムID"
                        },
                        "language": {
                            "type": "enum('ja','en','zh-Hant')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "名前"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "mst_items_i18n_mst_item_id_language_index": {
                            "type": "index",
                            "columns": [
                                "mst_item_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_achievements": {
                    "comment": "アチーブメントミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "達成回数"
                        },
                        "unlock_criterion_type": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "開放条件タイプ"
                        },
                        "unlock_criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "開放条件値"
                        },
                        "unlock_criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "開放条件の達成回数"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー。mission_full_completeのカウント対象となる。"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_rewards.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_achievement_dependencies": {
                    "comment": "アチーブメントミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "依存関係のグルーピングID"
                        },
                        "mst_mission_achievement_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_achievements.id"
                        },
                        "unlock_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "対象グループ内でのミッションの開放順。1つ前のunlock_orderを持つミッションをクリアしたら開放される。"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_group_id_mst_mission_achievement_id": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "mst_mission_achievement_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_group_id_unlock_order": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "unlock_order"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_achievements_i18n": {
                    "comment": "アチーブメントミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_mission_achievement_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_achievements.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_beginners": {
                    "comment": "初心者ミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "達成回数"
                        },
                        "unlock_day": {
                            "type": "smallint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "開始からの開放日"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー"
                        },
                        "bonus_point": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "ミッションボーナスポイント量"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_beginners_i18n": {
                    "comment": "初心者ミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_mission_beginner_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_beginners.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "title": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ダイアログタイトル"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "初心者ミッションテキスト"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_beginner_prompt_phrases_i18n": {
                    "comment": "初心者ミッションのUIの煽り文言の設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "prompt_phrase_text": {
                            "type": "text",
                            "nullable": false,
                            "comment": "設定文言"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "設定文言を表示する開始期間"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "設定文言を表示する終了期間"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_dailies": {
                    "comment": "デイリーミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "達成回数"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー"
                        },
                        "bonus_point": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "ミッションボーナスポイント量"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_daily_bonuses": {
                    "comment": "デイリーボーナス(ログボ)の設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mission_daily_bonus_type": {
                            "type": "enum('DailyBonus')",
                            "nullable": false,
                            "comment": "デイリーボーナスタイプ"
                        },
                        "login_day_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "条件とするログイン日数"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "表示順"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_type_login_day_count": {
                            "type": "unique",
                            "columns": [
                                "mission_daily_bonus_type",
                                "login_day_count"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_dailies_i18n": {
                    "comment": "デイリーミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_mission_daily_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_dailies.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_events": {
                    "comment": "イベントミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "イベントID"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "達成回数"
                        },
                        "unlock_criterion_type": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "開放条件タイプ"
                        },
                        "unlock_criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "開放条件値"
                        },
                        "unlock_criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "達成回数"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "event_category": {
                            "type": "enum('AdventBattle')",
                            "nullable": true,
                            "comment": "イベントカテゴリー"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_event_dailies": {
                    "comment": "イベントデイリーミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "イベントID"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "達成回数"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_event_daily_bonuses": {
                    "comment": "イベントデイリーボーナス(イベントログボ)の設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_mission_event_daily_bonus_schedule_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_event_daily_bonus_schedules.id"
                        },
                        "login_day_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "条件とするログイン日数"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "表示順"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_schedule_id_login_day_count": {
                            "type": "unique",
                            "columns": [
                                "mst_mission_event_daily_bonus_schedule_id",
                                "login_day_count"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_event_daily_bonus_schedules": {
                    "comment": "イベントデイリーボーナス(イベントログボ)のスケジュール設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_events.id"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日時"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "index_mst_event_id": {
                            "type": "index",
                            "columns": [
                                "mst_event_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_event_dailies_i18n": {
                    "comment": "イベントデイリーミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_mission_event_daily_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_events.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_event_dependencies": {
                    "comment": "イベントミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "依存関係のグルーピングID"
                        },
                        "mst_mission_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_events.id"
                        },
                        "unlock_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "グループ内でのミッションの開放順"
                        }
                    },
                    "indexes": {
                        "group_id_mst_mission_event_id_unique": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "mst_mission_event_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "group_id_unlock_order_unique": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "unlock_order"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_events_i18n": {
                    "comment": "イベントミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_mission_event_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_events.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_limited_terms": {
                    "comment": "期間限定ミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "progress_group_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "進捗グループ"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "達成回数"
                        },
                        "mission_category": {
                            "type": "enum('AdventBattle')",
                            "nullable": false,
                            "comment": "ミッションカテゴリー"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日時"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_limited_term_dependencies": {
                    "comment": "期間限定ミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "依存関係のグルーピングID"
                        },
                        "mst_mission_limited_term_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_limited_terms.id"
                        },
                        "unlock_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "グループ内でのミッションの開放順"
                        }
                    },
                    "indexes": {
                        "group_id_mst_mission_limited_term_id_unique": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "mst_mission_limited_term_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "group_id_unlock_order_unique": {
                            "type": "unique",
                            "columns": [
                                "group_id",
                                "unlock_order"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_limited_terms_i18n": {
                    "comment": "期間限定ミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_mission_limited_term_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_limited_terms.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        }
                    },
                    "indexes": {
                        "mst_mission_limited_term_id_language_unique": {
                            "type": "unique",
                            "columns": [
                                "mst_mission_limited_term_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_rewards": {
                    "comment": "ミッション報酬設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "報酬グルーピングID"
                        },
                        "resource_type": {
                            "type": "enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit')",
                            "nullable": false,
                            "comment": "報酬タイプ"
                        },
                        "resource_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "報酬リソースID"
                        },
                        "resource_amount": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "報酬の個数"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_weeklies": {
                    "comment": "ウィークリーミッションの設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "criterion_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "達成条件タイプ"
                        },
                        "criterion_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "達成条件値"
                        },
                        "criterion_count": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "達成回数"
                        },
                        "group_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "分類キー"
                        },
                        "bonus_point": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "ミッションボーナスポイント量"
                        },
                        "mst_mission_reward_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_reward_groups.group_id"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "並び順"
                        },
                        "destination_scene": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ミッションから遷移する画面"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_mission_weeklies_i18n": {
                    "comment": "ウィークリーミッションの設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_mission_weekly_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_mission_weeklies.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "説明"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_packs": {
                    "comment": "ショップのパック設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "product_sub_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "opr_products.id"
                        },
                        "discount_rate": {
                            "type": "smallint unsigned",
                            "nullable": false,
                            "comment": "割引率"
                        },
                        "pack_type": {
                            "type": "enum('Daily','Normal')",
                            "nullable": false,
                            "comment": "パック販売タイプ"
                        },
                        "sale_condition": {
                            "type": "enum('StageClear','UserLevel')",
                            "nullable": true,
                            "comment": "販売開始条件"
                        },
                        "sale_condition_value": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "販売開始条件値"
                        },
                        "sale_hours": {
                            "type": "smallint unsigned",
                            "nullable": true,
                            "comment": "条件達成からの販売時間"
                        },
                        "tradable_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "交換可能個数"
                        },
                        "cost_type": {
                            "type": "enum('Cash','Diamond','PaidDiamond','Ad','Free')",
                            "nullable": false,
                            "comment": "販売コスト種別"
                        },
                        "cost_amount": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "コスト量"
                        },
                        "is_recommend": {
                            "type": "tinyint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "おすすめフラグ"
                        },
                        "is_first_time_free": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "初回無料フラグ"
                        },
                        "is_display_expiration": {
                            "type": "tinyint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "表示期限があるかどうか"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "バナー画像パス"
                        },
                        "pack_decoration": {
                            "type": "enum('Gold')",
                            "nullable": true,
                            "comment": "パックの装飾"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "mst_packs_sale_condition_index": {
                            "type": "index",
                            "columns": [
                                "sale_condition"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_packs_i18n": {
                    "comment": "ショップのパック設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_pack_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_packs.id"
                        },
                        "language": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "パック名"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_pvps": {
                    "comment": "PVP情報のマスターテーブル",
                    "columns": {
                        "id": {
                            "type": "varchar(16)",
                            "nullable": false,
                            "comment": "西暦4桁と週番号2桁を使った自動採番IDを使用"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "ranking_min_pvp_rank_class": {
                            "type": "enum('Bronze','Silver','Gold','Platinum')",
                            "nullable": true,
                            "comment": "ランキングに含む最小PVPランク区分"
                        },
                        "max_daily_challenge_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "1日のアイテム消費なし挑戦可能回数"
                        },
                        "max_daily_item_challenge_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "1日のアイテム消費あり挑戦可能回数"
                        },
                        "item_challenge_cost_amount": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "アイテム消費あり挑戦時の消費アイテム数"
                        },
                        "initial_battle_point": {
                            "type": "int",
                            "nullable": false,
                            "comment": "初期バトルポイント"
                        },
                        "mst_in_game_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_in_games.id"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_pvps_i18n": {
                    "comment": "PVP情報の多言語対応テーブル",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "id"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "mst_pvp_id": {
                            "type": "varchar(16)",
                            "nullable": false,
                            "comment": "mst_pvps.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "PVP名"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "PVP説明"
                        }
                    },
                    "indexes": {
                        "mst_pvps_i18n_unique": {
                            "type": "unique",
                            "columns": [
                                "mst_pvp_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_quests": {
                    "comment": "クエスト設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "quest_type": {
                            "type": "enum('Normal','Event','Enhance','Tutorial')",
                            "nullable": false,
                            "comment": "クエストの種類"
                        },
                        "mst_event_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "mst_events.id"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "mst_series.id"
                        },
                        "sort_order": {
                            "type": "int",
                            "nullable": false,
                            "default": "0",
                            "comment": "ソート順序"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アセットキー"
                        },
                        "start_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日"
                        },
                        "end_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "quest_group": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "同クエストとして表示をまとめるグループ"
                        },
                        "difficulty": {
                            "type": "enum('Normal','Hard','Extra')",
                            "nullable": false,
                            "default": "Normal",
                            "comment": "難易度"
                        }
                    },
                    "indexes": {
                        "idx_mst_event_id": {
                            "type": "index",
                            "columns": [
                                "mst_event_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "idx_quest_type": {
                            "type": "index",
                            "columns": [
                                "quest_type"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_quests_i18n": {
                    "comment": "クエスト名などの多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_quest_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_quests.id"
                        },
                        "language": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "名前"
                        },
                        "category_name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "カテゴリ名"
                        },
                        "flavor_text": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "フレーバーテキスト"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_series": {
                    "comment": "ジャンプ+の漫画作品をGLOW内で識別するための情報の設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "作品ID"
                        },
                        "jump_plus_url": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ジャンプ+作品へのURL"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アセットキー"
                        },
                        "banner_asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "バナーのアセット"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_series_i18n": {
                    "comment": "作品の言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_series.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "作品名"
                        },
                        "prefix_word": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "絞込も文字(ア行など)"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_mst_series_id_language": {
                            "type": "unique",
                            "columns": [
                                "mst_series_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_shop_items": {
                    "comment": "ユーザーに販売する非課金商品を管理する",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "shop_type": {
                            "type": "enum('Coin','Daily','Weekly')",
                            "nullable": true,
                            "comment": "商品タイプ"
                        },
                        "cost_type": {
                            "type": "enum('Coin','Diamond','PaidDiamond','Ad','Free')",
                            "nullable": true,
                            "comment": "消費するコストのタイプ"
                        },
                        "cost_amount": {
                            "type": "int unsigned",
                            "nullable": true,
                            "default": "0",
                            "comment": "消費するコストの数量"
                        },
                        "is_first_time_free": {
                            "type": "tinyint(1)",
                            "nullable": false,
                            "comment": "初回無料か"
                        },
                        "tradable_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "交換可能回数"
                        },
                        "resource_type": {
                            "type": "enum('FreeDiamond','Coin','IdleCoin','Item')",
                            "nullable": true,
                            "comment": "獲得物のタイプ"
                        },
                        "resource_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "獲得物のID"
                        },
                        "resource_amount": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "comment": "獲得物の数量"
                        },
                        "start_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "販売開始日時"
                        },
                        "end_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "販売終了日時"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_shop_passes": {
                    "comment": "ショップパスの基本設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "opr_product_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "opr_products.id"
                        },
                        "is_display_expiration": {
                            "type": "tinyint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "販売の有効期限を表示するかどうか 0:表示しない 1:表示する"
                        },
                        "pass_duration_days": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "パスの有効日数"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "アセットキー"
                        },
                        "shop_pass_cell_color": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "パス表示バナーの背景色"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_opr_product_id": {
                            "type": "unique",
                            "columns": [
                                "opr_product_id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_shop_passes_i18n": {
                    "comment": "ショップパスの基本設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_shop_pass_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_shop_passes.id"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語設定"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "パス名"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_mst_shop_pass_id_language": {
                            "type": "unique",
                            "columns": [
                                "mst_shop_pass_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_stages": {
                    "comment": "ステージの基本設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_quest_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "クエストID(mst_quest.id)"
                        },
                        "mst_in_game_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "インゲーム設定ID(mst_in_game.id)"
                        },
                        "stage_number": {
                            "type": "int",
                            "nullable": false,
                            "default": "0",
                            "comment": "ステージ番号"
                        },
                        "recommended_level": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "おすすめレベル"
                        },
                        "cost_stamina": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "消費スタミナ"
                        },
                        "exp": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "獲得EXP"
                        },
                        "coin": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "獲得コイン"
                        },
                        "mst_artwork_fragment_drop_group_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "mst_artwork_fragments.drop_group_id"
                        },
                        "prev_mst_stage_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "解放条件のステージID"
                        },
                        "mst_stage_tips_group_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "tipsID"
                        },
                        "auto_lap_type": {
                            "type": "enum('AfterClear','Initial')",
                            "nullable": true,
                            "comment": "スタミナブーストタイプ"
                        },
                        "max_auto_lap_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "1",
                            "comment": "最大スタミナブースト周回指定可能数"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "ソート順序"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "ステージ公開開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "ステージ公開終了日時"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "アセットキー"
                        },
                        "release_key": {
                            "type": "bigint unsigned",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_stages_i18n": {
                    "comment": "ステージの基本設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_stage_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ステージID(mst_stage.id)"
                        },
                        "language": {
                            "type": "enum('ja','en','zh-Hant')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語設定"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ステージ名"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_units": {
                    "comment": "キャラ設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "fragment_mst_item_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "かけらID(mst_items.id)"
                        },
                        "color": {
                            "type": "enum('Colorless','Red','Blue','Yellow','Green')",
                            "nullable": false,
                            "default": "Colorless",
                            "comment": "属性"
                        },
                        "role_type": {
                            "type": "enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special')",
                            "nullable": false,
                            "comment": "属性"
                        },
                        "attack_range_type": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ロール"
                        },
                        "unit_label": {
                            "type": "enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR')",
                            "nullable": false,
                            "comment": "ラベル"
                        },
                        "has_specific_rank_up": {
                            "type": "tinyint",
                            "nullable": false,
                            "default": "0",
                            "comment": "キャラ個別のランクアップ設定を使うかどうか"
                        },
                        "mst_series_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "作品ID"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "アセットキー"
                        },
                        "rarity": {
                            "type": "enum('N','R','SR','SSR','UR')",
                            "nullable": false,
                            "comment": "レアリティ"
                        },
                        "sort_order": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "ソート順序"
                        },
                        "summon_cost": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "インゲーム召喚コスト"
                        },
                        "summon_cool_time": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "インゲーム召喚クールタイム"
                        },
                        "special_attack_initial_cool_time": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "召喚時の必殺ワザクールタイム"
                        },
                        "special_attack_cool_time": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "必殺ワザ使用後の必殺ワザクールタイム"
                        },
                        "min_hp": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "基礎最小ステータス"
                        },
                        "max_hp": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "基礎最大ステータス"
                        },
                        "damage_knock_back_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "被撃破までのHP減少によるノックバック回数"
                        },
                        "move_speed": {
                            "type": "decimal(10,2)",
                            "nullable": false,
                            "comment": "移動速度"
                        },
                        "well_distance": {
                            "type": "double(8,2)",
                            "nullable": false,
                            "comment": "索敵距離"
                        },
                        "min_attack_power": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "最小攻撃力"
                        },
                        "max_attack_power": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "最大攻撃力"
                        },
                        "mst_unit_ability_id1": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "リレーション向けMstAbilityId"
                        },
                        "ability_unlock_rank1": {
                            "type": "int",
                            "nullable": false,
                            "comment": "開放ランク"
                        },
                        "mst_unit_ability_id2": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "リレーション向けMstAbilityId"
                        },
                        "ability_unlock_rank2": {
                            "type": "int",
                            "nullable": false,
                            "comment": "開放ランク"
                        },
                        "mst_unit_ability_id3": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "リレーション向けMstAbilityId"
                        },
                        "ability_unlock_rank3": {
                            "type": "int",
                            "nullable": false,
                            "comment": "開放ランク"
                        },
                        "is_encyclopedia_special_attack_position_right": {
                            "type": "tinyint unsigned",
                            "nullable": false,
                            "default": "0",
                            "comment": "図鑑画面で必殺ワザ再生時にキャラを右寄りにするかフラグ"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "mst_units_i18n": {
                    "comment": "キャラ設定の多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_unit_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "リレーション向けMstUnitId"
                        },
                        "language": {
                            "type": "enum('ja','en','zh-Hant')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語設定"
                        },
                        "name": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "名前"
                        },
                        "description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "詳細"
                        },
                        "detail": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "情報詳細"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "opr_gachas": {
                    "comment": "ガシャの基本設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "gacha_type": {
                            "type": "enum('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal','Tutorial')",
                            "nullable": true,
                            "comment": "ガシャのタイプ"
                        },
                        "upper_group": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "None",
                            "comment": "天井設定区分"
                        },
                        "enable_ad_play": {
                            "type": "tinyint(1)",
                            "nullable": false,
                            "default": "0",
                            "comment": "広告で回せるか"
                        },
                        "enable_add_ad_play_upper": {
                            "type": "tinyint(1)",
                            "nullable": false,
                            "default": "0",
                            "comment": "広告で天井を動かすか"
                        },
                        "ad_play_interval_time": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "広告で回すことができるインターバル時間(設定単位は分)"
                        },
                        "multi_draw_count": {
                            "type": "int unsigned",
                            "nullable": false,
                            "default": "1",
                            "comment": "N連の指定"
                        },
                        "multi_fixed_prize_count": {
                            "type": "smallint unsigned",
                            "nullable": true,
                            "default": "0",
                            "comment": "N連の確定枠数"
                        },
                        "daily_play_limit_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "１日に回すことができる上限数"
                        },
                        "total_play_limit_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "回すことができる上限数"
                        },
                        "daily_ad_limit_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "1日に広告で回すことができる上限数"
                        },
                        "total_ad_limit_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "広告で回すことができる上限数"
                        },
                        "prize_group_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "opr_gacha_prizes.group_id"
                        },
                        "fixed_prize_group_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "確定枠(opr_gacha_prizes.group_id)"
                        },
                        "appearance_condition": {
                            "type": "enum('Always','HasTicket')",
                            "nullable": false,
                            "default": "Always",
                            "comment": "登場条件"
                        },
                        "unlock_condition_type": {
                            "type": "enum('None','MainPartTutorialComplete')",
                            "nullable": false,
                            "default": "None",
                            "comment": "開放条件タイプ"
                        },
                        "unlock_duration_hours": {
                            "type": "smallint unsigned",
                            "nullable": true,
                            "comment": "条件達成からの開放時間"
                        },
                        "start_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "開始日時"
                        },
                        "end_at": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "終了日時"
                        },
                        "display_mst_unit_id": {
                            "type": "text",
                            "nullable": true,
                            "comment": "表示に使用するピックアップユニットIDを指定する"
                        },
                        "display_information_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "ガチャ詳細用お知らせID"
                        },
                        "display_gacha_caution_id": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "comment": "ガシャ注意事項のid（adm_gacha_cautions.id）"
                        },
                        "gacha_priority": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "バナー表示順"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "created_at": {
                            "type": "timestamp",
                            "nullable": true,
                            "comment": "作成日時のタイムスタンプ"
                        },
                        "updated_at": {
                            "type": "timestamp",
                            "nullable": true,
                            "comment": "更新日時のタイムスタンプ"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "opr_gachas_i18n": {
                    "comment": "ガシャ名などの多言語設定",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "opr_gacha_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "opr_gachas.id"
                        },
                        "language": {
                            "type": "enum('ja','en','zh-Hant')",
                            "nullable": false,
                            "default": "ja",
                            "comment": "言語情報"
                        },
                        "name": {
                            "type": "text",
                            "nullable": true,
                            "comment": "ガチャ名"
                        },
                        "description": {
                            "type": "text",
                            "nullable": true,
                            "comment": "ガチャ説明"
                        },
                        "max_rarity_upper_description": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "default": "",
                            "comment": "最高レアリティ天井の文言"
                        },
                        "pickup_upper_description": {
                            "type": "varchar(255)",
                            "nullable": true,
                            "default": "",
                            "comment": "ピックアップ天井の文言"
                        },
                        "fixed_prize_description": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "確定枠の表示文言"
                        },
                        "banner_url": {
                            "type": "text",
                            "nullable": true,
                            "comment": "バナーURL"
                        },
                        "logo_asset_key": {
                            "type": "varchar(255)",
                            "nullable": true
                        },
                        "logo_banner_url": {
                            "type": "text",
                            "nullable": true,
                            "comment": "詳細へ飛んだ後のロゴバナーurl"
                        },
                        "gacha_background_color": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "ガチャ背景色"
                        },
                        "gacha_banner_size": {
                            "type": "enum('SizeM','SizeL')",
                            "nullable": false,
                            "default": "SizeM",
                            "comment": "ガチャバナーサイズ"
                        },
                        "release_key": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        },
                        "created_at": {
                            "type": "timestamp",
                            "nullable": true,
                            "comment": "作成日時のタイムスタンプ"
                        },
                        "updated_at": {
                            "type": "timestamp",
                            "nullable": true,
                            "comment": "更新日時のタイムスタンプ"
                        }
                    },
                    "indexes": {
                        "opr_gacha_id_unique": {
                            "type": "unique",
                            "columns": [
                                "opr_gacha_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "opr_products": {
                    "comment": "ユーザーに販売する実際の商品を管理する\n1mst_store_productに対して複数",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "UUID"
                        },
                        "mst_store_product_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "mst_store_products.id"
                        },
                        "product_type": {
                            "type": "enum('diamond','pack','pass')",
                            "nullable": true,
                            "comment": "商品タイプ"
                        },
                        "purchasable_count": {
                            "type": "int unsigned",
                            "nullable": true,
                            "comment": "購入可能回数"
                        },
                        "paid_amount": {
                            "type": "bigint",
                            "nullable": false,
                            "default": "0",
                            "comment": "配布する有償一次通貨"
                        },
                        "display_priority": {
                            "type": "int unsigned",
                            "nullable": false,
                            "comment": "表示優先度"
                        },
                        "start_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "販売開始日時"
                        },
                        "end_date": {
                            "type": "timestamp",
                            "nullable": false,
                            "comment": "販売終了日時"
                        },
                        "release_key": {
                            "type": "int",
                            "nullable": false,
                            "default": "1",
                            "comment": "リリースキー"
                        }
                    },
                    "indexes": {
                        "mst_store_product_id_index": {
                            "type": "index",
                            "columns": [
                                "mst_store_product_id"
                            ],
                            "index_type": "BTREE"
                        },
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                },
                "opr_products_i18n": {
                    "comment": "ユーザーに販売する実際の商品の多言語テーブル",
                    "columns": {
                        "id": {
                            "type": "varchar(255)",
                            "nullable": false
                        },
                        "opr_product_id": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "comment": "対象のプロダクト(opr_products.id)"
                        },
                        "language": {
                            "type": "enum('ja')",
                            "nullable": false,
                            "comment": "言語情報"
                        },
                        "asset_key": {
                            "type": "varchar(255)",
                            "nullable": false,
                            "default": "",
                            "comment": "アセットキー"
                        }
                    },
                    "indexes": {
                        "PRIMARY": {
                            "type": "primary",
                            "columns": [
                                "id"
                            ],
                            "index_type": "BTREE"
                        },
                        "uk_opr_product_id_language": {
                            "type": "unique",
                            "columns": [
                                "opr_product_id",
                                "language"
                            ],
                            "index_type": "BTREE"
                        }
                    }
                }
            }
        }
    }
}```

---

<!-- FILE: ./scripts/filter_master_tables_schema.py -->
## ./scripts/filter_master_tables_schema.py

```py
#!/usr/bin/env python3
"""
CSVファイル名に基づいてmaster_tables_schema.jsonをフィルタリングするスクリプト

Usage:
    python scripts/filter_master_tables_schema.py [--dry-run]
"""

import json
import re
import sys
from pathlib import Path
from typing import Dict, List, Set, Tuple


def camel_to_snake(name: str) -> str:
    """
    アッパーキャメルケースをスネークケースに変換する
    例: MstAdventBattle -> mst_advent_battle
    """
    # 連続する大文字を処理（例: I18n -> i18n）
    s1 = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', name)
    # 小文字の後の大文字を処理
    return re.sub('([a-z0-9])([A-Z])', r'\1_\2', s1).lower()


def get_csv_files(masterdata_dir: Path) -> List[str]:
    """
    glow-masterdataディレクトリ直下のCSVファイル名（拡張子なし）を取得する
    """
    csv_files = []
    for csv_path in masterdata_dir.glob('*.csv'):
        # 拡張子を除去
        csv_name = csv_path.stem
        csv_files.append(csv_name)
    return sorted(csv_files)


def pluralize(word: str) -> List[str]:
    """
    単数形の単語を複数形に変換する候補を生成する

    Args:
        word: 単数形の単語

    Returns:
        複数形の候補リスト
    """
    candidates = []

    # 1. そのまま（すでに複数形の場合）
    candidates.append(word)

    # 2. 末尾に 's' を追加
    candidates.append(f"{word}s")

    # 3. 末尾が 'y' で終わる場合、'ies' に変換
    if word.endswith('y'):
        candidates.append(f"{word[:-1]}ies")

    # 4. 末尾が 'x', 's', 'ch', 'sh' で終わる場合、'es' を追加
    if word.endswith(('x', 's', 'ch', 'sh')):
        candidates.append(f"{word}es")

    # 5. 末尾が 'fe' で終わる場合、'ves' に変換
    if word.endswith('fe'):
        candidates.append(f"{word[:-2]}ves")

    # 6. 末尾が 'f' で終わる場合、'ves' に変換
    if word.endswith('f') and not word.endswith('ff'):
        candidates.append(f"{word[:-1]}ves")

    return candidates


def csv_name_to_table_candidates(csv_name: str) -> List[str]:
    """
    CSVファイル名から可能性のあるテーブル名候補を生成する

    Args:
        csv_name: CSVファイル名（拡張子なし）例: MstAdventBattle, MstAdventBattleI18n

    Returns:
        候補となるテーブル名のリスト（スネークケース・複数形）
    """
    # I18nサフィックスを持つかチェック
    has_i18n_suffix = csv_name.endswith('I18n')

    if has_i18n_suffix:
        # I18nサフィックスを除去
        base_name = csv_name[:-4]  # 'I18n' を除去
        # ベース名をスネークケースに変換
        snake_base = camel_to_snake(base_name)
        # ベース名を複数形に変換して、_i18n を追加
        base_plurals = pluralize(snake_base)
        candidates = [f"{plural}_i18n" for plural in base_plurals]
    else:
        # 通常のテーブル名処理
        snake_name = camel_to_snake(csv_name)
        candidates = pluralize(snake_name)

    return candidates


def match_csv_to_table(csv_name: str, table_names: Set[str]) -> Tuple[str, str]:
    """
    CSVファイル名をJSONテーブル名にマッチングする

    Args:
        csv_name: CSVファイル名（拡張子なし）
        table_names: JSONに含まれるテーブル名のセット

    Returns:
        (csv_name, matched_table_name) または (csv_name, None)
    """
    candidates = csv_name_to_table_candidates(csv_name)

    for candidate in candidates:
        if candidate in table_names:
            return (csv_name, candidate)

    return (csv_name, None)


def filter_schema(
    input_json_path: Path,
    masterdata_dir: Path,
    output_json_path: Path,
    dry_run: bool = False
) -> None:
    """
    CSVファイル名に基づいてスキーマJSONをフィルタリングする
    """
    print("=" * 80)
    print("CSVファイルに基づくJSONスキーマフィルタリング")
    print("=" * 80)
    print()

    # JSONファイルを読み込む
    print(f"📖 JSONファイルを読み込み中: {input_json_path}")
    with open(input_json_path, 'r', encoding='utf-8') as f:
        schema_data = json.load(f)

    # テーブル名一覧を取得
    tables = schema_data.get('databases', {}).get('mst', {}).get('tables', {})
    table_names = set(tables.keys())
    print(f"   JSONに含まれるテーブル数: {len(table_names)}")
    print()

    # CSVファイル名一覧を取得
    print(f"📂 CSVファイルを検索中: {masterdata_dir}")
    csv_files = get_csv_files(masterdata_dir)
    print(f"   見つかったCSVファイル数: {len(csv_files)}")
    print()

    # マッチング処理
    print("🔍 マッチング処理開始")
    print("-" * 80)

    matched_tables = {}
    unmatched_csvs = []

    for csv_name in csv_files:
        csv_name_display, matched_table = match_csv_to_table(csv_name, table_names)

        if matched_table:
            matched_tables[matched_table] = csv_name_display
            print(f"✅ {csv_name_display:50s} → {matched_table}")
        else:
            unmatched_csvs.append(csv_name_display)
            print(f"❌ {csv_name_display:50s} → (マッチなし)")

    print("-" * 80)
    print()

    # 結果サマリー
    print("📊 マッチング結果")
    print(f"   マッチしたCSVファイル: {len(matched_tables)}/{len(csv_files)}")
    print(f"   マッチしなかったCSVファイル: {len(unmatched_csvs)}/{len(csv_files)}")
    print()

    if unmatched_csvs:
        print("⚠️  マッチしなかったCSVファイル:")
        for csv_name in unmatched_csvs:
            print(f"   - {csv_name}")
        print()

    # ドライランモードの場合はここで終了
    if dry_run:
        print("🏃 ドライランモードのため、ファイルは作成されません")
        print()
        return

    # フィルタリング済みのスキーマを作成
    filtered_tables = {
        table_name: tables[table_name]
        for table_name in matched_tables.keys()
    }

    filtered_schema = {
        'databases': {
            'mst': {
                'tables': filtered_tables
            }
        }
    }

    # 出力ファイルに書き込む
    print(f"💾 フィルタリング済みJSONを出力中: {output_json_path}")
    with open(output_json_path, 'w', encoding='utf-8') as f:
        json.dump(filtered_schema, f, ensure_ascii=False, indent=4)

    print(f"   出力されたテーブル数: {len(filtered_tables)}")
    print()
    print("✨ 完了しました！")
    print()


def main():
    """メイン処理"""
    # 引数チェック
    dry_run = '--dry-run' in sys.argv

    # パスの設定
    script_dir = Path(__file__).parent
    project_root = script_dir.parent

    input_json_path = project_root / 'projects/glow-server/api/database/schema/exports/master_tables_schema.json'
    masterdata_dir = project_root / 'projects/glow-masterdata'
    output_json_path = project_root / 'projects/glow-server/api/database/schema/exports/master_tables_schema_filtered.json'

    # ファイル存在チェック
    if not input_json_path.exists():
        print(f"❌ エラー: 入力JSONファイルが見つかりません: {input_json_path}", file=sys.stderr)
        sys.exit(1)

    if not masterdata_dir.exists():
        print(f"❌ エラー: masterdataディレクトリが見つかりません: {masterdata_dir}", file=sys.stderr)
        sys.exit(1)

    # フィルタリング処理実行
    filter_schema(input_json_path, masterdata_dir, output_json_path, dry_run)


if __name__ == '__main__':
    main()
```

---

<!-- FILE: ./マスタデータ/docs/ミッショントリガー一覧 - データ入力用ミッショントリガー一覧.csv -->
## ./マスタデータ/docs/ミッショントリガー一覧 - データ入力用ミッショントリガー一覧.csv

```csv
トリガー,機能カテゴリ,"使用条件
開放/達成/両方","条件タイプ
criterion_type","条件指定値
criterion_value (X)","条件回数
criterion_count (Y)",対象ミッションタイプ,"仕様書記載の
achievement_criterion_type",サーバーメモ
指定アイテムをX個集める,アイテム,達成条件,SpecificItemCollect,アイテムID（mst_items.id）,集めて欲しいアイテム個数,全て対象,specific_item_collect,
インゲームで敵を Y体撃破,インゲーム,達成条件,DefeatEnemyCount,NULL(未指定),"撃破して欲しい敵数。
期間中に撃破した数の合計値。",全て対象,"enemy_count
now_enemy_count",
インゲームで強敵を Y体撃破,インゲーム,達成条件,DefeatBossEnemyCount,NULL(未指定),"撃破して欲しい敵数。
期間中に撃破した数の合計値。",全て対象,"boss_enemy_count
now_boss_enemy_count",
指定ガシャXをY回引く,ガシャ,達成条件,SpecificGachaDrawCount,"ガチャID
（仮：opr_gachas.id）",ガシャを引いて欲しい回数,全て対象,"normal_gacha_completed
gacha_completed
target_gacha_count
gacha_completed
target_gacha_count",
通算でガチャをY回引く,ガシャ,達成条件,GachaDrawCount,NULL(未指定),通算でガシャを引いて欲しい回数,全て対象,all_gacha_count,
ゲートを X 回以上強化,ゲート,達成条件,OutpostEnhanceCount,NULL(未指定),ゲートを強化してほしい回数,全て対象,gate_enhance_count,
指定したゲート強化項目がLvYに到達する,ゲート,達成条件,SpecificOutpostEnhanceLevel,"対象の強化項目ID
（mst_outpost_enhancements.id）",対象の強化項目が到達してほしいレベル,全て対象,未定,
広告視聴をY回する,システム,達成条件,IaaCount,NULL(未指定),広告視聴してほしい回数,全て対象,iaa_count,
公式X（エックス）をフォローする,システム,達成条件,FollowCompleted,NULL(未指定),1,"アチーブメント
初心者",follow_completed,"挑戦を押したら無条件でクリアとするミッション。厳密なチェックは行わない。
mission/clear_on_call APIで達成できる。"
アカウント連携を行う,システム,達成条件,AccountCompleted,NULL(未指定),1,"アチーブメント
初心者",account_completed,"挑戦を押したら無条件でクリアとするミッション。厳密なチェックは行わない。
mission/clear_on_call APIで達成できる。"
"指定クエストを初クリアする

※ 2回以上クリアなどの、クリア回数指定はできません

クエストクリア = 内包するステージ全てを1回以上クリアしている",ステージ,両方,SpecificQuestClear,クエストID（mst_quests.id）,"1
(2以上は設定できません。未指定であってもサーバー処理側では1として処理します)

クエスト初クリア時に進捗。
クエストの初クリアの判定タイミングについて：
例えば、クエストAにステージ1,2,3があった場合、ステージ1,2をクリアしていた状態で、
ステージ3を初クリアした時に、クエストA初クリアとして判定する。",全て対象,"specific_quest_clear
quest_clear",
指定ステージXを Y 回クリア,ステージ,両方,SpecificStageClearCount,ステージID（mst_stages.id）,クリアしてほしい回数,全て対象,"specific_stage_clear
stage_clear",
指定ステージXにY回挑戦する,ステージ,達成条件,SpecificStageChallengeCount,ステージID（mst_stages.id）,挑戦してほしい回数,全て対象,specific_stage_challenge,
通算クエストクリア回数が Y 回に到達,ステージ,両方,QuestClearCount,NULL(未指定),全クエスト対象で、クリアして欲しいクエスト数。,全て対象,quest_clear_count,
通算ステージクリア回数が Y 回に到達,ステージ,両方,StageClearCount,NULL(未指定),"全ステージ対象で、ステージをクリアして欲しい回数。

同一ステージを複数回クリアした場合でも加算される。",全て対象,stage_clear_count,
"指定したユニットを編成して
指定したステージを Y回クリア",ステージ,達成条件,SpecificUnitStageClearCount,"ユニットID.ステージID
（2つのID文字列を「.(ドット)」で連結した文字列）

例：ユニットID=unit1,ステージID=stage2だった場合
unit1.stage2 と入力する。",指定ユニットを編成して、ステージをクリアして欲しい回数,全て対象,specific_unit_stage_clear_count,
"指定したユニットを編成して
指定したステージに Y回挑戦",ステージ,達成条件,SpecificUnitStageChallengeCount,↑と同じ設定方法,指定ユニットを編成して、ステージに挑戦して欲しい回数,全て対象,specific_unit_stage_challenge_count,
ミッションをY個クリアする,ミッション,達成条件,MissionClearCount,,"同じミッションタイプのミッションの内でクリアして欲しい数
※ MissionClearCount, SpecificMissionClearCountのミッションはカウントに含めない",全て対象,mission_clear_count,
指定したミッショングループXの内でY個クリアする,ミッション,達成条件,SpecificMissionClearCount,mst_mission_<ミッションタイプ>s.group_key,"同じミッションタイプの同じgroup_keyを持つミッションの内でクリアして欲しい数。
※ MissionClearCount, SpecificMissionClearCountのミッションはカウントに含めない

「指定されたミッションを全てクリア」を設定したい場合の設定例：
criterion_type=SpecificMissionClearCount, criterion_value=groupKey1, criterion_count=5と設定し、
クリアして欲しいミッションがmission1,2,3,4,5だった場合、
mission1,2,3,4,5のgroup_keyにgroupKey1と設定する",全て対象,mission_full_complete,
"ミッションボーナスポイントをY個集める
(ミッションの累計ボーナスポイントエリアの設定)",ミッション,達成条件,MissionBonusPoint,NULL(未指定),集めて欲しいミッションボーナスポイント数,"初心者
デイリー
ウィークリー",記載なし,
プレイヤーレベルがYに到達,ユーザー,両方,UserLevel,NULL(未指定),到達してほしいユーザーレベル,全て対象,player_rank,
コインをY個集める,ユーザー,達成条件,CoinCollect,NULL(未指定),集めてほしいコイン数,全て対象,coin_collect,
コインを X 枚使用した,ユーザー,達成条件,CoinUsedCount,NULL(未指定),使用してほしいコインの数,全て対象,coin_used_count,
ユニットのレベルアップをY回する,ユニット,達成条件,UnitLevelUpCount,NULL(未指定),"全ユニット対象で
レベルアップを実行してほしい回数",全て対象,unit_enhance_count,
全ユニットの内でいずれかがLv. Y に到達,ユニット,達成条件,UnitLevel,NULL(未指定),到達して欲しいユニットレベル,全て対象,unit_level,
指定ユニットがLv. Y に到達,ユニット,達成条件,SpecificUnitLevel,ユニットID（mst_units.id）,到達して欲しいユニットレベル,全て対象,specific_unit_level,
指定したユニットのランクアップ回数がY回以上,ユニット,達成条件,SpecificUnitRankUpCount,ユニットID（mst_units.id）,指定ユニットのランクアップをしてほしい回数,全て対象,unit_break_count,
指定したユニットのグレードアップ回数がY回以上,ユニット,達成条件,SpecificUnitGradeUpCount,ユニットID（mst_units.id）,指定ユニットのグレードアップをしてほしい回数,全て対象,unit_evolution_count,
ストアレビューを記載,システム,達成条件,ReviewCompleted,NULL(未指定),1,"アチーブメント
初心者",review_completed,"挑戦を押したら無条件でクリアとするミッション。厳密なチェックは行わない。
mission/clear_on_call APIで達成できる。"
通算ログインが Y日に到達,ログイン,両方,LoginCount,NULL(未指定),通算ログインして欲しい日数,全て対象,"login_count
now_login_count",
連続ログインが Y日目に到達,ログイン,達成条件,LoginContinueCount,NULL(未指定),連続ログインして欲しい日数,全て対象,login_continue_count,
"最終ログイン日から 
X 日後(カムバック判定)にログインし
その日からの連続ログイン日数が Y 日に到達",ログイン,達成条件,ComebackLoginContinueCount,カムバックするまでにかかった日数,カムバックした日からの連続ログイン日数,全て対象,comeback_login_continue_count,カムバックログボ機能が実装され不要になったので削除済み
"最終ログインから Y 日後（カムバック判定後）
に再ログイン",ログイン,開放条件,Comeback,NULL(未指定),カムバックするまでにかかった日数,全て対象,comeback,カムバックログボ機能が実装され不要になったので削除済み
クイック探索をY回する,探索,達成条件,IdleIncentiveQuickCount,NULL(未指定),クイック探索をして欲しい回数,全て対象,未定,
探索をY回する,探索,達成条件,IdleIncentiveCount,NULL(未指定),探索をして欲しい回数,全て対象,未定,
特定作品XのユニットをY体獲得しよう,ユニット,達成条件,SpecificSeriesUnitAcquiredCount,作品ID（mst_series.id）,獲得してほしいユニットの種類数（獲得済ユニットを再度獲得した際はカウントしない）,全て対象,,
ユニットを Y 体入手しよう,ユニット,達成条件,UnitAcquiredCount,NULL(未指定),"入手して欲しいユニット体数。
仮にたとえば、同じユニットを2体入手した場合は、+2になる",全て対象,unit_acquired_count,
指定ユニットXをY体獲得しよう,ユニット,達成条件,SpecificUnitAcquiredCount,ユニットID（mst_units.id）,獲得してほしい体数（獲得済みでもカウントする）,全て対象,,
指定作品Xの敵キャラをY体発見しよう,インゲーム,達成条件,SpecificSeriesEnemyDiscoveryCount,作品ID（mst_series.id）,発見してほしいエネミーの種類数（発見済ユニットを再度発見した際はカウントしない）,全て対象,,
敵キャラをY体発見しよう,インゲーム,達成条件,EnemyDiscoveryCount,NULL(未指定),発見してほしいエネミーの種類数（発見済ユニットを再度発見した際はカウントしない）,全て対象,,
敵キャラXをY体発見しよう,インゲーム,達成条件,SpecificEnemyDiscoveryCount,エネミーID（mst_enemy_characters.id）,発見してほしい体数（発見済みでもカウントする）,全て対象,,
指定作品XのエンブレムをY個獲得しよう,図鑑,達成条件,SpecificSeriesEmblemAcquiredCount,作品ID（mst_series.id）,獲得してほしいエンブレムの種類数（獲得済で再度獲得してもカウントしない）,全て対象,,
エンブレムをY個獲得しよう,図鑑,達成条件,EmblemAcquiredCount,NULL(未指定),獲得してほしいエンブレムの種類数（獲得済で再度獲得してもカウントしない）,全て対象,,
指定エンブレムXをYつ獲得しよう,図鑑,達成条件,SpecificEmblemAcquiredCount,エンブレムID（mst_emblems.id）,獲得してほしい個数（獲得済みでもカウントする）,全て対象,,
指定作品Xの原画をYつ完成させよう,図鑑,達成条件,SpecificSeriesArtworkCompletedCount,作品ID（mst_series.id）,完成させてほしい原画数,全て対象,,
原画をYつ完成させよう,図鑑,達成条件,ArtworkCompletedCount,NULL(未指定),完成させてほしい原画数,全て対象,,
指定原画Xを1つ完成させよう,図鑑,達成条件,SpecificArtworkCompletedCount,原画ID（mst_artworks.id）,完成させてほしい原画数（原画完成は生涯1回のみなので、実質1のみ設定可）,全て対象,,
降臨バトルを X 回挑戦する,降臨バトル,達成条件,AdventBattleChallengeCount,NULL(未指定),挑戦してほしい回数,全て対象,,
降臨バトルの累計スコアが X 達成,降臨バトル,達成条件,AdventBattleTotalScore,NULL(未指定),降臨バトルに複数回挑戦した時の累計として獲得してほしいスコア,全て対象,,
降臨バトルのハイスコアが X 達成,降臨バトル,達成条件,AdventBattleScore,NULL(未指定),"獲得してほしいハイスコア。
降臨バトルに複数回挑戦した場合、その内で最大のスコアが記録され判定対象となる。",全て対象,,
決闘にY回挑戦しよう,決闘,達成条件,PvpChallengeCount,NULL(未指定),挑戦してほしい回数,全て対象,,
決闘にY回勝利しよう,決闘,達成条件,PvpWinCount,NULL(未指定),勝利してほしい回数,全て対象,,
"指定のWEBサイトにアクセスしよう

例：「ジャンブルラッシュ情報局」を確認しよう",システム,達成条件,AccessWeb,"リンク先のURL

※ 達成条件判定には未使用。
　 アプリ上での表示にのみ使用。",1,"アチーブメント
初心者",,"挑戦を押したら無条件でクリアとするミッション。厳密なチェックは行わない。
mission/clear_on_call APIで達成できる。"```

---

