#!/usr/bin/env python3
"""
GLOW ID検証スクリプト

指定されたIDが正しい命名規則に従っているかを検証します。
"""

import re
import sys
from typing import Dict, List, Tuple, Optional

# カテゴリー別の検証ルール
VALIDATION_RULES: Dict[str, Dict] = {
    "作品": {
        "pattern": r"^[a-z]{3}$",
        "max_length": 3,
        "description": "3文字の小文字英字"
    },
    "キャラ": {
        "pattern": r"^chara_[a-z]{3}_\d{5}$",
        "max_length": 15,
        "description": "chara_{作品ID3桁}_{5桁番号}"
    },
    "敵キャラ": {
        "pattern": r"^enemy_[a-z]{3}_\d{5}$",
        "max_length": 15,
        "description": "enemy_{作品ID3桁}_{5桁番号}"
    },
    "アバターアイコン": {
        "pattern": r"^unit_icon_chara_[a-z]{3}_\d{5}$",
        "max_length": 25,
        "description": "unit_icon_chara_{作品ID3桁}_{5桁番号}"
    },
    "キャラアイコン（プレイヤー）": {
        "pattern": r"^(picon|eicon)_chara_[a-z]{3}_\d{5}$",
        "max_length": 21,
        "description": "(picon|eicon)_chara_{作品ID3桁}_{5桁番号}"
    },
    "キャラアイコン（敵）": {
        "pattern": r"^eicon_enemy_[a-z]{3}_\d{5}$",
        "max_length": 21,
        "description": "eicon_enemy_{作品ID3桁}_{5桁番号}"
    },
    "クエスト": {
        "pattern": r"^quest_\w+_(normal|hard|expert|null)_[a-z]{3}_\d{5}$",
        "max_length": 32,
        "description": "quest_{カテゴリー}_{難易度}_{作品ID3桁}_{5桁番号}"
    },
    "アイテム": {
        "pattern": r"^\w+_[a-z]{3}_\d{5}$",
        "max_length": 19,
        "description": "{接頭語}_{作品ID3桁}_{5桁番号}"
    },
    "図鑑": {
        "pattern": r"^book_[a-z]{3}_\d{5}$",
        "max_length": 14,
        "description": "book_{作品ID3桁}_{5桁番号}"
    },
    "エンブレム": {
        "pattern": r"^emblem_\w+_[a-z]{3}_\d{5}$",
        "max_length": 24,
        "description": "emblem_{カテゴリー}_{作品ID3桁}_{5桁番号}"
    },
    "背景": {
        "pattern": r"^background_[a-z]{3}_\d{5}$",
        "max_length": 20,
        "description": "background_{作品ID3桁}_{5桁番号}"
    },
    "コンテンツ": {
        "pattern": r"^contents_(event|training|duel|raid|limited)_[a-z]{3}_\d{5}$",
        "max_length": 24,
        "description": "contents_{カテゴリー}_{作品ID3桁}_{5桁番号}"
    },
    "BGM": {
        "pattern": r"^SBG_\d{3}_\d{3}$",
        "max_length": 11,
        "description": "SBG_{画面ID3桁}_{連番3桁}"
    },
    "ゲート": {
        "pattern": r"^outpost_[a-z]{3}_\d{5}$",
        "max_length": 17,
        "description": "outpost_{作品ID3桁}_{5桁番号}"
    }
}


def detect_category(id_string: str) -> Optional[str]:
    """IDの接頭語からカテゴリーを自動検出"""
    if re.match(r"^[a-z]{3}$", id_string):
        return "作品"
    elif id_string.startswith("chara_"):
        return "キャラ"
    elif id_string.startswith("enemy_"):
        return "敵キャラ"
    elif id_string.startswith("unit_icon_chara_"):
        return "アバターアイコン"
    elif id_string.startswith("picon_chara_") or id_string.startswith("eicon_chara_"):
        return "キャラアイコン（プレイヤー）"
    elif id_string.startswith("eicon_enemy_"):
        return "キャラアイコン（敵）"
    elif id_string.startswith("quest_"):
        return "クエスト"
    elif id_string.startswith("book_"):
        return "図鑑"
    elif id_string.startswith("emblem_"):
        return "エンブレム"
    elif id_string.startswith("background_"):
        return "背景"
    elif id_string.startswith("contents_"):
        return "コンテンツ"
    elif id_string.startswith("SBG_"):
        return "BGM"
    elif id_string.startswith("outpost_"):
        return "ゲート"
    else:
        return "アイテム"  # デフォルト


def validate_id(id_string: str, category: Optional[str] = None) -> Tuple[bool, List[str]]:
    """
    IDを検証し、結果とエラーメッセージのリストを返す

    Args:
        id_string: 検証するID文字列
        category: カテゴリー（指定しない場合は自動検出）

    Returns:
        (is_valid, error_messages)
    """
    errors = []

    # カテゴリー自動検出
    if category is None:
        category = detect_category(id_string)
        if category is None:
            errors.append("カテゴリーを自動検出できませんでした")
            return False, errors

    # ルール取得
    if category not in VALIDATION_RULES:
        errors.append(f"未知のカテゴリー: {category}")
        return False, errors

    rule = VALIDATION_RULES[category]

    # 桁数チェック
    if len(id_string) > rule["max_length"]:
        errors.append(
            f"桁数超過: {len(id_string)}桁（最大{rule['max_length']}桁）"
        )

    # パターンマッチング
    if not re.match(rule["pattern"], id_string):
        errors.append(
            f"フォーマット不正: 期待される形式は '{rule['description']}'"
        )

    # 作品IDチェック（BGMと作品ID以外）
    if category not in ["BGM", "作品"]:
        work_id_match = re.search(r"_([a-z]{3})_\d{5}$", id_string)
        if work_id_match:
            work_id = work_id_match.group(1)
            # 既知の作品IDリスト（簡易チェック）
            if not re.match(r"^[a-z]{3}$", work_id):
                errors.append(f"作品ID '{work_id}' が不正な形式です")

    return len(errors) == 0, errors


def main():
    """CLI実行時のエントリーポイント"""
    if len(sys.argv) < 2:
        print("使用法: python validate_id.py <ID> [category]")
        print("\n利用可能なカテゴリー:")
        for cat in VALIDATION_RULES.keys():
            print(f"  - {cat}")
        sys.exit(1)

    id_string = sys.argv[1]
    category = sys.argv[2] if len(sys.argv) > 2 else None

    is_valid, errors = validate_id(id_string, category)

    detected_category = category or detect_category(id_string)
    print(f"検証ID: {id_string}")
    print(f"カテゴリー: {detected_category}")

    if is_valid:
        print("✅ 検証成功: IDは正しい形式です")
        sys.exit(0)
    else:
        print("❌ 検証失敗:")
        for error in errors:
            print(f"  - {error}")
        sys.exit(1)


if __name__ == "__main__":
    main()
