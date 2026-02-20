#!/usr/bin/env python3
"""
ID採番ルール提案生成スクリプト
通算連番を使用しているテーブルに対して、新しいID採番ルールを提案する
"""

import csv
import re
from pathlib import Path
from typing import Dict, List, Tuple

# テーブルごとの提案ルールマッピング
TABLE_SPECIFIC_PROPOSALS = {
    # ミッション系
    "MstMissionReward": {
        "pattern": "[group_id]",
        "reason": "既存のgroup_idカラムの値をそのままIDとして使用。group_idは既に意味のある識別子（例: daily_bonus_reward_1_1）なので、通算連番は不要。各グループごとに一意のIDが割り当てられる。"
    },
    "MstMissionLimitedTerm": {
        "pattern": "mission_limited_term_[作品ID]_[イベントID]_[連番]",
        "reason": "作品IDとイベントIDをベースにした採番。既存のmst_mission_reward_group_id（例: kai_00001_limited_term_1）と同様の構造。イベントごとに連番を振ることで、過去の最大値を考慮する必要がなくなる。"
    },
    "MstMissionAchievement": {
        "pattern": "mission_achievement_[criterion_type]_[連番]",
        "reason": "達成条件タイプ（criterion_type）ごとに連番を振る。バージョン番号（achievement_2_）は削除し、条件タイプで分類することで、より意味のあるIDとする。"
    },
    "MstMissionDailyBonus": {
        "pattern": "mission_daily_bonus_[login_day_count]",
        "reason": "ログイン日数（login_day_count）をIDに含める。日数ごとに一意なので通算連番は不要。例: mission_daily_bonus_1, mission_daily_bonus_2, ..."
    },
    "MstMissionAchievementDependency": {
        "pattern": "mission_achievement_dependency_[parent_id]_[child_id]",
        "reason": "依存関係を表すテーブルなので、親IDと子IDの組み合わせをIDとする。通算連番は不要。"
    },
    "MstMissionEventDependency": {
        "pattern": "mission_event_dependency_[parent_id]_[child_id]",
        "reason": "依存関係を表すテーブルなので、親IDと子IDの組み合わせをIDとする。通算連番は不要。"
    },

    # カムバックボーナス系
    "MstComebackBonus": {
        "pattern": "comeback_bonus_[schedule_id]_[login_day_count]",
        "reason": "スケジュールIDとログイン日数の組み合わせでIDを構成。バージョン番号（comeback_1_）は削除し、より明確な構造にする。"
    },
    "MstDailyBonusReward": {
        "pattern": "daily_bonus_reward_[group_id]",
        "reason": "既存のmst_daily_bonus_reward_group_idカラムの値をそのままIDとして使用。グループIDが既に一意の識別子なので通算連番は不要。"
    },

    # フラグメント・アイテム系
    "MstFragmentBox": {
        "pattern": "fragment_box_[mst_item_id]",
        "reason": "アイテムIDをベースにしたID。1つのアイテムに1つのフラグメントボックスが対応するため、アイテムIDを使うことで一意性を保証。"
    },
    "MstItemRarityTrade": {
        "pattern": "item_rarity_trade_[from_rarity]_to_[to_rarity]",
        "reason": "交換元と交換先のレアリティをIDに含める。レアリティの組み合わせで一意に決まるため、通算連番は不要。"
    },
    "MstItemTransition": {
        "pattern": "item_transition_[from_item_id]_to_[to_item_id]",
        "reason": "アイテム変換元と変換先のアイテムIDをIDに含める。変換ルールごとに一意に決まるため、通算連番は不要。"
    },

    # アイドルインセンティブ系
    "MstIdleIncentiveItem": {
        "pattern": "idle_incentive_item_[tier]_[連番]",
        "reason": "ティア（tier）ごとに連番を振る。ティアごとに報酬が管理されるため、ティア別の連番が適切。"
    },
    "MstIdleIncentiveReward": {
        "pattern": "idle_incentive_reward_[tier]_[連番]",
        "reason": "ティア（tier）ごとに連番を振る。ティアごとに報酬が管理されるため、ティア別の連番が適切。"
    },

    # ダミーユーザー系
    "MstDummyOutpost": {
        "pattern": "dummy_outpost_[連番]",
        "reason": "ダミーゲートは固定数のマスタデータなので、シンプルなプレフィックス + 連番で十分。プレフィックスを追加することで他のIDと区別しやすくする。"
    },
    "MstDummyUserI18n": {
        "pattern": "dummy_user_i18n_[user_id]",
        "reason": "ダミーユーザーIDをベースにしたID。1ユーザーに1つのi18nレコードが対応するため、ユーザーIDを使うことで一意性を保証。"
    },
    "MstDummyUserUnit": {
        "pattern": "dummy_user_unit_[user_id]_[unit_id]",
        "reason": "ダミーユーザーIDとユニットIDの組み合わせでIDを構成。ユーザーとユニットの組み合わせで一意に決まる。"
    },

    # イベント系
    "MstEventBonusUnit": {
        "pattern": "event_bonus_unit_[event_id]_[unit_id]",
        "reason": "イベントIDとユニットIDの組み合わせでIDを構成。イベントごとにボーナスユニットが管理されるため、イベント別のIDが適切。"
    },
    "MstExchangeI18n": {
        "pattern": "exchange_i18n_[exchange_id]",
        "reason": "交換マスタIDをベースにしたID。1つの交換に1つのi18nレコードが対応するため、交換IDを使うことで一意性を保証。"
    },

    # InGame系
    "MstInGameSpecialRuleUnitStatus": {
        "pattern": "ingame_special_rule_unit_status_[rule_id]_[unit_id]",
        "reason": "特殊ルールIDとユニットIDの組み合わせでIDを構成。ルールとユニットの組み合わせで一意に決まる。"
    },

    # NG/ホワイトワード
    "MstNgWord": {
        "pattern": "ng_word_[word_hash]",
        "reason": "ワードのハッシュ値または単語そのものをIDとする。通算連番ではなく、単語ベースのIDにすることで、重複を避けやすくする。"
    },
    "MstWhiteWord": {
        "pattern": "white_word_[word_hash]",
        "reason": "ワードのハッシュ値または単語そのものをIDとする。通算連番ではなく、単語ベースのIDにすることで、重複を避けやすくする。"
    },

    # 拠点強化
    "MstOutpostEnhancement": {
        "pattern": "outpost_enhancement_[outpost_type]_[enhancement_type]_[連番]",
        "reason": "拠点タイプと強化タイプごとに連番を振る。バージョン番号（enhance_1_）は削除し、より明確な構造にする。"
    },

    # パック系
    "MstPackContent": {
        "pattern": "pack_content_[pack_id]_[連番]",
        "reason": "パックIDごとに連番を振る。各パックごとにコンテンツが管理されるため、パック別の連番が適切。"
    },

    # PVP系
    "MstPvpDummy": {
        "pattern": "pvp_dummy_[rank_tier]_[連番]",
        "reason": "ランクティアごとに連番を振る。ティアごとにダミーが管理されるため、ティア別の連番が適切。"
    },

    # クエスト系
    "MstQuestBonusUnit": {
        "pattern": "quest_bonus_unit_[quest_id]_[unit_id]",
        "reason": "クエストIDとユニットIDの組み合わせでIDを構成。クエストごとにボーナスユニットが管理されるため、クエスト別のIDが適切。"
    },
    "MstQuestEventBonusSchedule": {
        "pattern": "quest_event_bonus_schedule_[event_id]_[連番]",
        "reason": "イベントIDごとに連番を振る。イベントごとにスケジュールが管理されるため、イベント別の連番が適切。"
    },

    # Tips系
    "MstResultTipI18n": {
        "pattern": "result_tip_i18n_[tip_category]_[連番]",
        "reason": "Tipカテゴリーごとに連番を振る。カテゴリーごとにTipsが管理されるため、カテゴリー別の連番が適切。"
    },
    "MstTutorialTipI18n": {
        "pattern": "tutorial_tip_i18n_[tip_category]_[連番]",
        "reason": "Tipカテゴリーごとに連番を振る。カテゴリーごとにTipsが管理されるため、カテゴリー別の連番が適切。"
    },

    # ショップパス
    "MstShopPass": {
        "pattern": "shop_pass_[pass_type]_[連番]",
        "reason": "パスタイプごとに連番を振る。パスタイプ（プレミアム、ノーマル等）ごとに管理されるため、タイプ別の連番が適切。"
    },
    "MstShopPassEffect": {
        "pattern": "shop_pass_effect_[pass_id]_[連番]",
        "reason": "パスIDごとに連番を振る。各パスごとに効果が管理されるため、パス別の連番が適切。"
    },

    # スペシャルアタック
    "MstSpecialAttackI18n": {
        "pattern": "special_attack_i18n_[attack_id]",
        "reason": "スペシャルアタックIDをベースにしたID。1つのアタックに1つのi18nレコードが対応するため、アタックIDを使うことで一意性を保証。"
    },

    # スピーチバルーン
    "MstSpeechBalloonI18n": {
        "pattern": "speech_balloon_i18n_[balloon_id]",
        "reason": "スピーチバルーンIDをベースにしたID。1つのバルーンに1つのi18nレコードが対応するため、バルーンIDを使うことで一意性を保証。"
    },

    # ステージ系
    "MstStageEnhanceRewardParam": {
        "pattern": "stage_enhance_reward_param_[stage_level]_[連番]",
        "reason": "ステージレベルごとに連番を振る。レベルごとに報酬パラメータが管理されるため、レベル別の連番が適切。"
    },
    "MstStageEventReward": {
        "pattern": "stage_event_reward_[event_id]_[stage_id]_[連番]",
        "reason": "イベントIDとステージIDごとに連番を振る。イベントとステージごとに報酬が管理されるため、それらの組み合わせが適切。"
    },
    "MstStageEventSetting": {
        "pattern": "stage_event_setting_[event_id]_[連番]",
        "reason": "イベントIDごとに連番を振る。イベントごとに設定が管理されるため、イベント別の連番が適切。"
    },
    "MstStageReward": {
        "pattern": "stage_reward_[stage_id]_[連番]",
        "reason": "ステージIDごとに連番を振る。ステージごとに報酬が管理されるため、ステージ別の連番が適切。"
    },

    # ストア商品
    "MstStoreProduct": {
        "pattern": "store_product_[product_category]_[連番]",
        "reason": "商品カテゴリーごとに連番を振る。カテゴリーごとに商品が管理されるため、カテゴリー別の連番が適切。"
    },

    # ユニット系
    "MstUnitEncyclopediaEffect": {
        "pattern": "unit_encyclopedia_effect_[rank]_[連番]",
        "reason": "ランクごとに連番を振る。図鑑ランクごとに効果が管理されるため、ランク別の連番が適切。"
    },
    "MstUnitEncyclopediaReward": {
        "pattern": "unit_encyclopedia_reward_[rank]_[連番]",
        "reason": "ランクごとに連番を振る。図鑑ランクごとに報酬が管理されるため、ランク別の連番が適切。"
    },
    "MstUnitFragmentConvert": {
        "pattern": "unit_fragment_convert_[from_fragment_id]_to_[to_fragment_id]",
        "reason": "変換元と変換先のフラグメントIDをIDに含める。変換ルールごとに一意に決まるため、通算連番は不要。"
    },
    "MstUnitGradeCoefficient": {
        "pattern": "unit_grade_coefficient_[grade]",
        "reason": "グレード（ランク）をIDとする。グレードごとに1つの係数が対応するため、グレードそのものをIDとして使用。"
    },
    "MstUnitGradeUp": {
        "pattern": "unit_grade_up_[from_grade]_to_[to_grade]",
        "reason": "昇格元と昇格先のグレードをIDに含める。昇格ルールごとに一意に決まるため、通算連番は不要。"
    },
    "MstUnitLevelUp": {
        "pattern": "unit_level_up_[level]",
        "reason": "レベルをIDとする。レベルごとに1つのレベルアップ情報が対応するため、レベルそのものをIDとして使用。"
    },
    "MstUnitRankCoefficient": {
        "pattern": "unit_rank_coefficient_[rank]",
        "reason": "ランクをIDとする。ランクごとに1つの係数が対応するため、ランクそのものをIDとして使用。"
    },
    "MstUnitRankUp": {
        "pattern": "unit_rank_up_[from_rank]_to_[to_rank]",
        "reason": "ランクアップ元と先のランクをIDに含める。ランクアップルールごとに一意に決まるため、通算連番は不要。"
    },
    "MstUnitRoleBonus": {
        "pattern": "unit_role_bonus_[role]",
        "reason": "ロール（役割）をIDとする。ロールごとに1つのボーナスが対応するため、ロールそのものをIDとして使用。"
    },

    # ユーザーレベル系
    "MstUserLevel": {
        "pattern": "user_level_[level]",
        "reason": "レベルをIDとする。レベルごとに1つのレベル情報が対応するため、レベルそのものをIDとして使用。"
    },
    "MstUserLevelBonus": {
        "pattern": "user_level_bonus_[level]",
        "reason": "レベルをIDとする。レベルごとに1つのボーナスが対応するため、レベルそのものをIDとして使用。"
    },
    "MstUserLevelBonusGroup": {
        "pattern": "user_level_bonus_group_[group_id]",
        "reason": "グループIDをIDとする。グループごとに1つのボーナスグループが対応するため、グループIDそのものをIDとして使用。"
    },

    # Oprテーブル系
    "OprGachaDisplayUnitI18n": {
        "pattern": "gacha_display_unit_i18n_[gacha_id]_[unit_id]",
        "reason": "ガチャIDとユニットIDの組み合わせでIDを構成。ガチャごとに表示ユニットが管理されるため、ガチャとユニットの組み合わせが適切。"
    },
    "OprGachaUpper": {
        "pattern": "gacha_upper_[gacha_id]_[upper_type]_[連番]",
        "reason": "ガチャIDとアッパータイプごとに連番を振る。ガチャごとにアッパー情報が管理されるため、ガチャ別の連番が適切。"
    },
    "OprGachaUseResource": {
        "pattern": "gacha_use_resource_[gacha_id]_[resource_type]_[連番]",
        "reason": "ガチャIDとリソースタイプごとに連番を振る。ガチャごとに使用リソースが管理されるため、ガチャとリソースタイプの組み合わせが適切。"
    },
    "OprProduct": {
        "pattern": "product_[product_category]_[連番]",
        "reason": "商品カテゴリーごとに連番を振る。カテゴリーごとに商品が管理されるため、カテゴリー別の連番が適切。"
    },
}


def generate_proposals(input_csv: Path, output_csv: Path):
    """
    ID採番パターン分析結果から提案を生成
    """
    with open(input_csv, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        rows = list(reader)

    # 提案列を追加
    for row in rows:
        table = row['テーブル']
        pattern = row['パターン']

        # テーブル固有の提案があれば使用
        if table in TABLE_SPECIFIC_PROPOSALS:
            proposal = TABLE_SPECIFIC_PROPOSALS[table]
            row['提案パターン'] = proposal['pattern']
            row['提案理由'] = proposal['reason']
        else:
            # デフォルト提案（カテゴリ別連番への変更を推奨）
            row['提案パターン'] = "要調査: テーブル構造を確認してカテゴリー別連番への変更を検討"
            row['提案理由'] = "このテーブルの詳細な構造を確認し、適切なカテゴリー（group_id、event_id等）を特定する必要があります。通算連番から意味のあるカテゴリー別連番への変更を推奨します。"

    # 結果をCSV出力
    with open(output_csv, 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['テーブル', '列', 'パターン', '説明', '提案パターン', '提案理由']
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)

    print(f"提案を生成しました: {output_csv}")
    print(f"総テーブル数: {len(rows)}")
    print(f"テーブル固有の提案数: {len([r for r in rows if r['テーブル'] in TABLE_SPECIFIC_PROPOSALS])}")


def main():
    input_csv = Path("domain/tasks/masterdata-entry/id-numbering-analysis/results/id_pattern_analysis.csv")
    output_csv = Path("domain/tasks/masterdata-entry/id-numbering-analysis/results/id_pattern_proposal.csv")

    generate_proposals(input_csv, output_csv)


if __name__ == "__main__":
    main()
