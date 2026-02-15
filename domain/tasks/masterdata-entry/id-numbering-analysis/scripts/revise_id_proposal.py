#!/usr/bin/env python3
"""
ID採番ルール提案を修正するスクリプト
- カテゴリ別連番の問題を修正
- 想定文字数列を追加
"""

import csv
import re
from pathlib import Path
from typing import Dict

# テーブルごとの修正提案
REVISED_PROPOSALS = {
    # カテゴリ別連番の問題があるテーブルを修正
    "MstResultTipI18n": {
        "pattern": "result_tip_i18n_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。リリースキー単位でデータが追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "result_tip_i18n_202509010_999 → 30文字"
    },
    "MstStoreProduct": {
        "pattern": "store_product_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。商品はリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "store_product_202509010_999 → 28文字"
    },
    "MstTutorialTipI18n": {
        "pattern": "tutorial_tip_i18n_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。リリースキー単位でデータが追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "tutorial_tip_i18n_202509010_999 → 33文字"
    },
    "OprProduct": {
        "pattern": "opr_product_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。運営商品はリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "opr_product_202509010_999 → 26文字"
    },

    # その他、カテゴリ別連番の問題があるテーブル
    "MstIdleIncentiveItem": {
        "pattern": "idle_incentive_item_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。報酬アイテムはリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "idle_incentive_item_202509010_999 → 35文字"
    },
    "MstIdleIncentiveReward": {
        "pattern": "idle_incentive_reward_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。報酬はリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "idle_incentive_reward_202509010_999 → 37文字"
    },
    "MstEventBonusUnit": {
        "pattern": "event_bonus_unit_[event_id]_[unit_id]",
        "reason": "イベントIDとユニットIDの組み合わせでIDを構成。イベントとユニットの組み合わせで一意に決まるため、連番は不要。既存のevent_idとunit_idを使うことで、過去の最大値を確認する必要がない。",
        "max_length": "event_bonus_unit_event_kai_1_chara_kai_00701 → 47文字"
    },
    "MstQuestBonusUnit": {
        "pattern": "quest_bonus_unit_[quest_id]_[unit_id]",
        "reason": "クエストIDとユニットIDの組み合わせでIDを構成。クエストとユニットの組み合わせで一意に決まるため、連番は不要。既存のIDを使うことで、過去の最大値を確認する必要がない。",
        "max_length": "quest_bonus_unit_quest_main_normal_dan_00001_chara_dan_00301 → 60文字"
    },
    "MstQuestEventBonusSchedule": {
        "pattern": "quest_event_bonus_schedule_[event_id]_[連番]",
        "reason": "イベントIDごとに連番を振る。イベント単位でスケジュールが追加されるため、イベント別の連番が適切。同じイベント内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "quest_event_bonus_schedule_event_kai_1_999 → 45文字"
    },
    "MstStageEventReward": {
        "pattern": "stage_event_reward_[event_id]_[stage_id]_[連番]",
        "reason": "イベントIDとステージIDごとに連番を振る。イベントとステージの組み合わせ単位で報酬が追加されるため、過去の最大値を確認する必要がない。",
        "max_length": "stage_event_reward_event_kai_1_stage_01_999 → 48文字"
    },
    "MstStageEventSetting": {
        "pattern": "stage_event_setting_[event_id]_[連番]",
        "reason": "イベントIDごとに連番を振る。イベント単位で設定が追加されるため、イベント別の連番が適切。同じイベント内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "stage_event_setting_event_kai_1_999 → 38文字"
    },
    "MstStageReward": {
        "pattern": "stage_reward_[stage_id]_[連番]",
        "reason": "ステージIDごとに連番を振る。ステージ単位で報酬が追加されるため、ステージ別の連番が適切。同じステージ内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "stage_reward_quest_main_normal_dan_00001_999 → 46文字"
    },
    "MstStageEnhanceRewardParam": {
        "pattern": "stage_enhance_reward_param_[enhance_level]",
        "reason": "強化レベルをIDとする。レベルごとに1つのパラメータが対応するため、レベルそのものをIDとして使用。連番は不要。",
        "max_length": "stage_enhance_reward_param_999 → 32文字"
    },
    "MstShopPass": {
        "pattern": "shop_pass_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。パスはリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "shop_pass_202509010_999 → 24文字"
    },
    "MstShopPassEffect": {
        "pattern": "shop_pass_effect_[pass_id]_[連番]",
        "reason": "パスIDごとに連番を振る。各パス単位で効果が追加されるため、パス別の連番が適切。同じパス内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "shop_pass_effect_shop_pass_202509010_999_999 → 48文字"
    },
    "MstPvpDummy": {
        "pattern": "pvp_dummy_[release_key]_[連番]",
        "reason": "リリースキーごとに連番を振る。ダミーデータはリリースキー単位で追加されるため、リリースキー別の連番が適切。同じリリース内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "pvp_dummy_202509010_999 → 24文字"
    },
    "MstUnitEncyclopediaEffect": {
        "pattern": "unit_encyclopedia_effect_[rank]",
        "reason": "図鑑ランクをIDとする。ランクごとに1つの効果が対応するため、ランクそのものをIDとして使用。連番は不要。",
        "max_length": "unit_encyclopedia_effect_999 → 29文字"
    },
    "MstUnitEncyclopediaReward": {
        "pattern": "unit_encyclopedia_reward_[rank]",
        "reason": "図鑑ランクをIDとする。ランクごとに1つの報酬が対応するため、ランクそのものをIDとして使用。連番は不要。",
        "max_length": "unit_encyclopedia_reward_999 → 29文字"
    },
    "MstPackContent": {
        "pattern": "pack_content_[pack_id]_[連番]",
        "reason": "パックIDごとに連番を振る。各パック単位でコンテンツが追加されるため、パック別の連番が適切。同じパック内では連番が1から始まるため、過去の最大値を確認する必要がない。",
        "max_length": "pack_content_start_chara_pack_1_999 → 38文字"
    },
    "OprGachaDisplayUnitI18n": {
        "pattern": "gacha_display_unit_i18n_[gacha_id]_[unit_id]",
        "reason": "ガチャIDとユニットIDの組み合わせでIDを構成。ガチャとユニットの組み合わせで一意に決まるため、連番は不要。",
        "max_length": "gacha_display_unit_i18n_gacha_kai_00001_chara_kai_00701 → 58文字"
    },
    "OprGachaUpper": {
        "pattern": "gacha_upper_[gacha_id]_[upper_type]_[連番]",
        "reason": "ガチャIDとアッパータイプごとに連番を振る。ガチャとタイプの組み合わせ単位で追加されるため、過去の最大値を確認する必要がない。",
        "max_length": "gacha_upper_gacha_kai_00001_upper_type_1_999 → 47文字"
    },
    "OprGachaUseResource": {
        "pattern": "gacha_use_resource_[gacha_id]_[resource_type]_[連番]",
        "reason": "ガチャIDとリソースタイプごとに連番を振る。ガチャとリソースタイプの組み合わせ単位で追加されるため、過去の最大値を確認する必要がない。",
        "max_length": "gacha_use_resource_gacha_kai_00001_FreeDiamond_999 → 55文字"
    },
}


def calculate_max_length(pattern: str) -> str:
    """
    提案パターンから想定最大文字数を計算
    """
    # パターン内の変数を実際の値例に置き換えて文字数を計算
    sample = pattern

    # よく使われる変数の想定最大値
    replacements = {
        r'\[release_key\]': '202509010',  # 9文字
        r'\[event_id\]': 'event_kai_1',  # 13文字（最大想定）
        r'\[quest_id\]': 'quest_main_normal_dan_00001',  # 28文字
        r'\[stage_id\]': 'stage_01',  # 8文字
        r'\[gacha_id\]': 'gacha_kai_00001',  # 16文字
        r'\[unit_id\]': 'chara_kai_00701',  # 16文字
        r'\[user_id\]': '999',  # 3文字
        r'\[pack_id\]': 'start_chara_pack_1',  # 19文字
        r'\[pass_id\]': 'shop_pass_202509010_999',  # 24文字
        r'\[group_id\]': 'daily_bonus_reward_1_1',  # 23文字
        r'\[mst_item_id\]': 'box_glo_00001',  # 13文字
        r'\[schedule_id\]': 'comeback_daily_bonus_1',  # 23文字
        r'\[login_day_count\]': '7',  # 2文字
        r'\[criterion_type\]': 'FollowCompleted',  # 15文字
        r'\[parent_id\]': '999',  # 3文字
        r'\[child_id\]': '999',  # 3文字
        r'\[作品ID\]': 'kai',  # 3文字
        r'\[イベントID\]': '00001',  # 5文字
        r'\[tier\]': '999',  # 3文字
        r'\[tip_category\]': 'battle',  # 6文字
        r'\[product_category\]': 'diamond',  # 7文字
        r'\[level\]': '100',  # 3文字
        r'\[rank\]': '999',  # 3文字
        r'\[grade\]': '99',  # 2文字
        r'\[role\]': 'attacker',  # 8文字
        r'\[from_rarity\]': '5',  # 1文字
        r'\[to_rarity\]': '6',  # 1文字
        r'\[from_item_id\]': 'item_glo_00001',  # 14文字
        r'\[to_item_id\]': 'item_glo_00002',  # 14文字
        r'\[from_fragment_id\]': 'fragment_glo_00001',  # 19文字
        r'\[to_fragment_id\]': 'fragment_glo_00002',  # 19文字
        r'\[from_grade\]': '5',  # 1文字
        r'\[to_grade\]': '6',  # 1文字
        r'\[from_rank\]': '5',  # 1文字
        r'\[to_rank\]': '6',  # 1文字
        r'\[rule_id\]': 'rule_001',  # 8文字
        r'\[resource_type\]': 'FreeDiamond',  # 11文字
        r'\[upper_type\]': 'upper_type_1',  # 12文字
        r'\[enhance_level\]': '999',  # 3文字
        r'\[word_hash\]': 'abcdef123456',  # 12文字
        r'\[連番\]': '999',  # 3文字
    }

    for pattern_var, replacement in replacements.items():
        sample = re.sub(pattern_var, replacement, sample)

    return f"{len(sample)}文字（例: {sample}）"


def revise_proposals(input_csv: Path, output_csv: Path):
    """
    提案を修正して新しいCSVを生成
    """
    with open(input_csv, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        rows = list(reader)

    # 提案を修正
    for row in rows:
        table = row['テーブル']

        # 修正提案がある場合は適用
        if table in REVISED_PROPOSALS:
            revision = REVISED_PROPOSALS[table]
            row['提案パターン'] = revision['pattern']
            row['提案理由'] = revision['reason']
            row['想定最大文字数'] = revision['max_length']
        else:
            # 修正提案がない場合は、想定文字数のみ計算
            row['想定最大文字数'] = calculate_max_length(row['提案パターン'])

    # 結果をCSV出力
    with open(output_csv, 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['テーブル', '列', '現在のパターン', '説明', '提案パターン', '提案理由', '想定最大文字数']
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)

    print(f"修正完了: {len(rows)}件")
    print(f"修正適用: {len([r for r in rows if r['テーブル'] in REVISED_PROPOSALS])}件")
    print(f"出力先: {output_csv}")


def main():
    base_dir = Path('domain/tasks/masterdata-entry/id-numbering-analysis/results')
    input_csv = base_dir / 'id_pattern_proposal_summary.csv'
    output_csv = base_dir / 'id_pattern_proposal_summary_v2.csv'

    revise_proposals(input_csv, output_csv)


if __name__ == "__main__":
    main()
