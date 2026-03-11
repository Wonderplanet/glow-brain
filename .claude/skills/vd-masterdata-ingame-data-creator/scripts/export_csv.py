#!/usr/bin/env python3
"""
VD Ingame Masterdata CSV エクスポートスクリプト

処理内容:
  1. 既存の MstEnemyStageParameter.csv を SQLite にインポート
  2. --enemy-ids で指定した ID のみをフィルタして MstEnemyStageParameter.csv をエクスポート
  3. 他の5テーブル（既に INSERT 済み）を CSV エクスポート
  4. 全テーブルの先頭に ENABLE=e を付加、NULL値は空文字列で出力

使用方法:
  python3 export_csv.py \\
    --db path/to/ingame.db \\
    --enemy-csv path/to/MstEnemyStageParameter.csv \\
    --enemy-ids "id1,id2,id3" \\
    --out path/to/generated/
"""

import argparse
import csv
import io
import os
import sqlite3
import sys


# =============================================================
# CSVエクスポート列定義（sheet_schema準拠・ENABLE除く）
# =============================================================

ENEMY_STAGE_PARAM_COLS = [
    # CSVでの列順（release_key が id より前）
    # ASエイリアスでキャメルケースに変換する3カラムに注意
    ("release_key", "release_key"),
    ("id", "id"),
    ("mst_enemy_character_id", "mst_enemy_character_id"),
    ("character_unit_kind", "character_unit_kind"),
    ("role_type", "role_type"),
    ("color", "color"),
    ("sort_order", "sort_order"),
    ("hp", "hp"),
    ("damage_knock_back_count", "damage_knock_back_count"),
    ("move_speed", "move_speed"),
    ("well_distance", "well_distance"),
    ("attack_power", "attack_power"),
    ("attack_combo_cycle", "attack_combo_cycle"),
    ("mst_unit_ability_id1", "mst_unit_ability_id1"),
    ("drop_battle_point", "drop_battle_point"),
    # キャメルケースエイリアス
    ("mst_transformation_enemy_stage_parameter_id", "mstTransformationEnemyStageParameterId"),
    ("transformation_condition_type", "transformationConditionType"),
    ("transformation_condition_value", "transformationConditionValue"),
]

ENEMY_OUTPOST_COLS = [
    ("id", "id"),
    ("hp", "hp"),
    ("is_damage_invalidation", "is_damage_invalidation"),
    ("outpost_asset_key", "outpost_asset_key"),
    ("artwork_asset_key", "artwork_asset_key"),
    ("release_key", "release_key"),
]

PAGE_COLS = [
    ("id", "id"),
    ("release_key", "release_key"),
]

KOMA_LINE_COLS = [
    ("id", "id"),
    ("mst_page_id", "mst_page_id"),
    ("row", "row"),
    ("height", "height"),
    ("koma_line_layout_asset_key", "koma_line_layout_asset_key"),
    ("koma1_asset_key", "koma1_asset_key"),
    ("koma1_width", "koma1_width"),
    ("koma1_back_ground_offset", "koma1_back_ground_offset"),
    ("koma1_effect_type", "koma1_effect_type"),
    ("koma1_effect_parameter1", "koma1_effect_parameter1"),
    ("koma1_effect_parameter2", "koma1_effect_parameter2"),
    ("koma1_effect_target_side", "koma1_effect_target_side"),
    ("koma1_effect_target_colors", "koma1_effect_target_colors"),
    ("koma1_effect_target_roles", "koma1_effect_target_roles"),
    ("koma2_asset_key", "koma2_asset_key"),
    ("koma2_width", "koma2_width"),
    ("koma2_back_ground_offset", "koma2_back_ground_offset"),
    ("koma2_effect_type", "koma2_effect_type"),
    ("koma2_effect_parameter1", "koma2_effect_parameter1"),
    ("koma2_effect_parameter2", "koma2_effect_parameter2"),
    ("koma2_effect_target_side", "koma2_effect_target_side"),
    ("koma2_effect_target_colors", "koma2_effect_target_colors"),
    ("koma2_effect_target_roles", "koma2_effect_target_roles"),
    ("koma3_asset_key", "koma3_asset_key"),
    ("koma3_width", "koma3_width"),
    ("koma3_back_ground_offset", "koma3_back_ground_offset"),
    ("koma3_effect_type", "koma3_effect_type"),
    ("koma3_effect_parameter1", "koma3_effect_parameter1"),
    ("koma3_effect_parameter2", "koma3_effect_parameter2"),
    ("koma3_effect_target_side", "koma3_effect_target_side"),
    ("koma3_effect_target_colors", "koma3_effect_target_colors"),
    ("koma3_effect_target_roles", "koma3_effect_target_roles"),
    ("koma4_asset_key", "koma4_asset_key"),
    ("koma4_width", "koma4_width"),
    ("koma4_back_ground_offset", "koma4_back_ground_offset"),
    ("koma4_effect_type", "koma4_effect_type"),
    ("koma4_effect_parameter1", "koma4_effect_parameter1"),
    ("koma4_effect_parameter2", "koma4_effect_parameter2"),
    ("koma4_effect_target_side", "koma4_effect_target_side"),
    ("koma4_effect_target_colors", "koma4_effect_target_colors"),
    ("koma4_effect_target_roles", "koma4_effect_target_roles"),
    ("release_key", "release_key"),
]

AUTO_PLAYER_SEQ_COLS = [
    ("id", "id"),
    ("sequence_set_id", "sequence_set_id"),
    ("sequence_group_id", "sequence_group_id"),
    ("sequence_element_id", "sequence_element_id"),
    ("priority_sequence_element_id", "priority_sequence_element_id"),
    ("condition_type", "condition_type"),
    ("condition_value", "condition_value"),
    ("action_type", "action_type"),
    ("action_value", "action_value"),
    ("action_value2", "action_value2"),
    ("summon_count", "summon_count"),
    ("summon_interval", "summon_interval"),
    ("summon_animation_type", "summon_animation_type"),
    ("summon_position", "summon_position"),
    ("move_start_condition_type", "move_start_condition_type"),
    ("move_start_condition_value", "move_start_condition_value"),
    ("move_stop_condition_type", "move_stop_condition_type"),
    ("move_stop_condition_value", "move_stop_condition_value"),
    ("move_restart_condition_type", "move_restart_condition_type"),
    ("move_restart_condition_value", "move_restart_condition_value"),
    ("move_loop_count", "move_loop_count"),
    ("is_summon_unit_outpost_damage_invalidation", "is_summon_unit_outpost_damage_invalidation"),
    ("last_boss_trigger", "last_boss_trigger"),
    ("aura_type", "aura_type"),
    ("death_type", "death_type"),
    ("enemy_hp_coef", "enemy_hp_coef"),
    ("enemy_attack_coef", "enemy_attack_coef"),
    ("enemy_speed_coef", "enemy_speed_coef"),
    ("override_drop_battle_point", "override_drop_battle_point"),
    ("defeated_score", "defeated_score"),
    ("action_delay", "action_delay"),
    ("deactivation_condition_type", "deactivation_condition_type"),
    ("deactivation_condition_value", "deactivation_condition_value"),
    ("release_key", "release_key"),
]

# MstInGame: 通常カラム + i18n2カラム（LEFT JOIN）
IN_GAME_COLS = [
    ("ig.id", "id"),
    ("ig.mst_auto_player_sequence_id", "mst_auto_player_sequence_id"),
    ("ig.mst_auto_player_sequence_set_id", "mst_auto_player_sequence_set_id"),
    ("ig.bgm_asset_key", "bgm_asset_key"),
    ("ig.boss_bgm_asset_key", "boss_bgm_asset_key"),
    ("ig.loop_background_asset_key", "loop_background_asset_key"),
    ("ig.player_outpost_asset_key", "player_outpost_asset_key"),
    ("ig.mst_page_id", "mst_page_id"),
    ("ig.mst_enemy_outpost_id", "mst_enemy_outpost_id"),
    ("ig.mst_defense_target_id", "mst_defense_target_id"),
    ("ig.boss_mst_enemy_stage_parameter_id", "boss_mst_enemy_stage_parameter_id"),
    ("ig.boss_count", "boss_count"),
    ("ig.normal_enemy_hp_coef", "normal_enemy_hp_coef"),
    ("ig.normal_enemy_attack_coef", "normal_enemy_attack_coef"),
    ("ig.normal_enemy_speed_coef", "normal_enemy_speed_coef"),
    ("ig.boss_enemy_hp_coef", "boss_enemy_hp_coef"),
    ("ig.boss_enemy_attack_coef", "boss_enemy_attack_coef"),
    ("ig.boss_enemy_speed_coef", "boss_enemy_speed_coef"),
    ("ig.release_key", "release_key"),
    # i18n (LEFT JOIN, language='ja')
    ("i18n.result_tips", "result_tips.ja"),
    ("i18n.description", "description.ja"),
]


# =============================================================
# 既存MstEnemyStageParameter CSVのカラムマッピング
# CSV列名 → DBカラム名
# =============================================================
ENEMY_CSV_TO_DB = {
    "ENABLE": None,  # スキップ
    "release_key": "release_key",
    "id": "id",
    "mst_enemy_character_id": "mst_enemy_character_id",
    "character_unit_kind": "character_unit_kind",
    "role_type": "role_type",
    "color": "color",
    "sort_order": "sort_order",
    "hp": "hp",
    "damage_knock_back_count": "damage_knock_back_count",
    "move_speed": "move_speed",
    "well_distance": "well_distance",
    "attack_power": "attack_power",
    "attack_combo_cycle": "attack_combo_cycle",
    "mst_unit_ability_id1": "mst_unit_ability_id1",
    "drop_battle_point": "drop_battle_point",
    # キャメルケース → スネークケース
    "mstTransformationEnemyStageParameterId": "mst_transformation_enemy_stage_parameter_id",
    "transformationConditionType": "transformation_condition_type",
    "transformationConditionValue": "transformation_condition_value",
}


def null_to_empty(value):
    """None を空文字列に変換する"""
    if value is None:
        return ""
    return str(value)


def import_enemy_stage_parameters(conn, enemy_csv_path):
    """既存の MstEnemyStageParameter.csv を SQLite にインポートする"""
    print(f"MstEnemyStageParameter をインポート中: {enemy_csv_path}")

    if not os.path.exists(enemy_csv_path):
        print(f"エラー: ファイルが見つかりません: {enemy_csv_path}", file=sys.stderr)
        sys.exit(1)

    # 既存データをクリア（冪等性確保）
    conn.execute("DELETE FROM mst_enemy_stage_parameters")

    db_cols = [c for c in [
        "id", "release_key", "mst_enemy_character_id", "character_unit_kind",
        "role_type", "color", "sort_order", "hp", "damage_knock_back_count",
        "move_speed", "well_distance", "attack_power", "attack_combo_cycle",
        "mst_unit_ability_id1", "drop_battle_point",
        "mst_transformation_enemy_stage_parameter_id",
        "transformation_condition_type", "transformation_condition_value",
    ]]

    placeholders = ",".join(["?" for _ in db_cols])
    insert_sql = f"INSERT OR REPLACE INTO mst_enemy_stage_parameters ({','.join(db_cols)}) VALUES ({placeholders})"

    count = 0
    with open(enemy_csv_path, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        for row in reader:
            values = []
            for db_col in db_cols:
                # 逆引きでCSVカラム名を探す
                csv_col = None
                for k, v in ENEMY_CSV_TO_DB.items():
                    if v == db_col:
                        csv_col = k
                        break
                if csv_col and csv_col in row:
                    raw = row[csv_col].strip()
                    values.append(None if raw == "" else raw)
                else:
                    values.append(None)
            conn.execute(insert_sql, values)
            count += 1

    conn.commit()
    print(f"  → {count} 行インポート完了")


def export_table_to_csv(conn, out_dir, filename, header_cols, select_sql, enable_col=True):
    """テーブルをCSVにエクスポートする

    Args:
        conn: SQLite接続
        out_dir: 出力ディレクトリ
        filename: 出力ファイル名
        header_cols: CSVヘッダー列名リスト
        select_sql: SELECTクエリ
        enable_col: ENABLE=e列を先頭に追加するか
    """
    os.makedirs(out_dir, exist_ok=True)
    out_path = os.path.join(out_dir, filename)

    cursor = conn.execute(select_sql)
    rows = cursor.fetchall()

    with open(out_path, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f, lineterminator="\n")
        # ヘッダー
        if enable_col:
            writer.writerow(["ENABLE"] + header_cols)
        else:
            writer.writerow(header_cols)
        # データ行
        for row in rows:
            values = [null_to_empty(v) for v in row]
            if enable_col:
                writer.writerow(["e"] + values)
            else:
                writer.writerow(values)

    print(f"  → {filename}: {len(rows)} 行")
    return len(rows)


def export_all(conn, enemy_ids, out_dir):
    """全テーブルをCSVエクスポートする"""
    print(f"\nCSVエクスポート先: {out_dir}")
    counts = {}

    # --- MstEnemyStageParameter ---
    ids_placeholder = ",".join([f"'{eid.strip()}'" for eid in enemy_ids])
    enemy_select = f"""
        SELECT
            release_key,
            id,
            mst_enemy_character_id,
            character_unit_kind,
            role_type,
            color,
            sort_order,
            hp,
            damage_knock_back_count,
            move_speed,
            well_distance,
            attack_power,
            attack_combo_cycle,
            mst_unit_ability_id1,
            drop_battle_point,
            mst_transformation_enemy_stage_parameter_id,
            transformation_condition_type,
            transformation_condition_value
        FROM mst_enemy_stage_parameters
        WHERE id IN ({ids_placeholder})
        ORDER BY sort_order, id
    """
    enemy_headers = [alias for _, alias in ENEMY_STAGE_PARAM_COLS]
    counts["MstEnemyStageParameter.csv"] = export_table_to_csv(
        conn, out_dir, "MstEnemyStageParameter.csv", enemy_headers, enemy_select
    )

    # --- MstEnemyOutpost ---
    outpost_select = """
        SELECT id, hp, is_damage_invalidation, outpost_asset_key, artwork_asset_key, release_key
        FROM mst_enemy_outposts
        ORDER BY id
    """
    outpost_headers = [alias for _, alias in ENEMY_OUTPOST_COLS]
    counts["MstEnemyOutpost.csv"] = export_table_to_csv(
        conn, out_dir, "MstEnemyOutpost.csv", outpost_headers, outpost_select
    )

    # --- MstPage ---
    page_select = """
        SELECT id, release_key
        FROM mst_pages
        ORDER BY id
    """
    page_headers = [alias for _, alias in PAGE_COLS]
    counts["MstPage.csv"] = export_table_to_csv(
        conn, out_dir, "MstPage.csv", page_headers, page_select
    )

    # --- MstKomaLine ---
    koma_select = """
        SELECT
            id, mst_page_id, row, height, koma_line_layout_asset_key,
            koma1_asset_key, koma1_width, koma1_back_ground_offset,
            koma1_effect_type, koma1_effect_parameter1, koma1_effect_parameter2,
            koma1_effect_target_side, koma1_effect_target_colors, koma1_effect_target_roles,
            koma2_asset_key, koma2_width, koma2_back_ground_offset,
            koma2_effect_type, koma2_effect_parameter1, koma2_effect_parameter2,
            koma2_effect_target_side, koma2_effect_target_colors, koma2_effect_target_roles,
            koma3_asset_key, koma3_width, koma3_back_ground_offset,
            koma3_effect_type, koma3_effect_parameter1, koma3_effect_parameter2,
            koma3_effect_target_side, koma3_effect_target_colors, koma3_effect_target_roles,
            koma4_asset_key, koma4_width, koma4_back_ground_offset,
            koma4_effect_type, koma4_effect_parameter1, koma4_effect_parameter2,
            koma4_effect_target_side, koma4_effect_target_colors, koma4_effect_target_roles,
            release_key
        FROM mst_koma_lines
        ORDER BY mst_page_id, row
    """
    koma_headers = [alias for _, alias in KOMA_LINE_COLS]
    counts["MstKomaLine.csv"] = export_table_to_csv(
        conn, out_dir, "MstKomaLine.csv", koma_headers, koma_select
    )

    # --- MstAutoPlayerSequence ---
    aps_select = """
        SELECT
            id, sequence_set_id, sequence_group_id, sequence_element_id,
            priority_sequence_element_id, condition_type, condition_value,
            action_type, action_value, action_value2,
            summon_count, summon_interval, summon_animation_type, summon_position,
            move_start_condition_type, move_start_condition_value,
            move_stop_condition_type, move_stop_condition_value,
            move_restart_condition_type, move_restart_condition_value,
            move_loop_count, is_summon_unit_outpost_damage_invalidation,
            last_boss_trigger, aura_type, death_type,
            enemy_hp_coef, enemy_attack_coef, enemy_speed_coef,
            override_drop_battle_point, defeated_score, action_delay,
            deactivation_condition_type, deactivation_condition_value,
            release_key
        FROM mst_auto_player_sequences
        ORDER BY sequence_set_id, CAST(sequence_element_id AS INTEGER), id
    """
    aps_headers = [alias for _, alias in AUTO_PLAYER_SEQ_COLS]
    counts["MstAutoPlayerSequence.csv"] = export_table_to_csv(
        conn, out_dir, "MstAutoPlayerSequence.csv", aps_headers, aps_select
    )

    # --- MstInGame（i18nをLEFT JOINで付加）---
    ingame_select = """
        SELECT
            ig.id,
            ig.mst_auto_player_sequence_id,
            ig.mst_auto_player_sequence_set_id,
            ig.bgm_asset_key,
            ig.boss_bgm_asset_key,
            ig.loop_background_asset_key,
            ig.player_outpost_asset_key,
            ig.mst_page_id,
            ig.mst_enemy_outpost_id,
            ig.mst_defense_target_id,
            ig.boss_mst_enemy_stage_parameter_id,
            ig.boss_count,
            ig.normal_enemy_hp_coef,
            ig.normal_enemy_attack_coef,
            ig.normal_enemy_speed_coef,
            ig.boss_enemy_hp_coef,
            ig.boss_enemy_attack_coef,
            ig.boss_enemy_speed_coef,
            ig.release_key,
            i18n.result_tips,
            i18n.description
        FROM mst_in_games ig
        LEFT JOIN mst_in_games_i18n i18n
            ON ig.id = i18n.mst_in_game_id AND i18n.language = 'ja'
        ORDER BY ig.id
    """
    ingame_headers = [alias for _, alias in IN_GAME_COLS]
    counts["MstInGame.csv"] = export_table_to_csv(
        conn, out_dir, "MstInGame.csv", ingame_headers, ingame_select
    )

    return counts


def main():
    parser = argparse.ArgumentParser(
        description="VD Ingame Masterdata CSV エクスポートスクリプト"
    )
    parser.add_argument("--db", required=True, help="SQLiteデータベースのパス")
    parser.add_argument(
        "--enemy-csv",
        required=True,
        help="MstEnemyStageParameterの既存CSVパス",
    )
    parser.add_argument(
        "--enemy-ids",
        required=True,
        help="エクスポートするMstEnemyStageParameter IDのカンマ区切りリスト",
    )
    parser.add_argument("--out", required=True, help="CSV出力ディレクトリ")
    args = parser.parse_args()

    enemy_ids = [eid.strip() for eid in args.enemy_ids.split(",") if eid.strip()]
    if not enemy_ids:
        print("エラー: --enemy-ids が空です", file=sys.stderr)
        sys.exit(1)

    print(f"対象MstEnemyStageParameter ID: {enemy_ids}")

    conn = sqlite3.connect(args.db)
    try:
        # 1. MstEnemyStageParameterをインポート
        import_enemy_stage_parameters(conn, args.enemy_csv)

        # 2. 全テーブルをCSVエクスポート
        counts = export_all(conn, enemy_ids, args.out)

        # 3. サマリー出力
        print("\n=== エクスポート完了 ===")
        total = 0
        for fname, count in counts.items():
            print(f"  {fname}: {count} 行")
            total += count
        print(f"  合計: {total} 行")
        print(f"  出力先: {args.out}")

    except sqlite3.IntegrityError as e:
        print(f"\nSQLite制約エラー: {e}", file=sys.stderr)
        print("CHECK制約違反の可能性があります。INSERT文の値を確認してください。", file=sys.stderr)
        sys.exit(1)
    finally:
        conn.close()


if __name__ == "__main__":
    main()
