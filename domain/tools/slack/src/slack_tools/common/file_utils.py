"""ファイル操作ユーティリティ"""

import json
import re
from pathlib import Path
from typing import Any


def sanitize_filename(name: str) -> str:
    """ファイル名として安全な文字列に変換

    Args:
        name: 元の文字列

    Returns:
        サニタイズされた文字列
    """
    # 危険な文字を_に置換
    safe_name = re.sub(r'[<>:"/\\|?*]', "_", name)
    # スペースをアンダースコアに
    safe_name = safe_name.replace(" ", "_")
    # 連続するアンダースコアを1つに
    safe_name = re.sub(r"_+", "_", safe_name)
    # 先頭・末尾のアンダースコアを削除
    return safe_name.strip("_")


def ensure_directory(path: Path) -> None:
    """ディレクトリが存在することを保証（存在しない場合は作成）

    Args:
        path: ディレクトリパス
    """
    path.mkdir(parents=True, exist_ok=True)


def save_json(path: Path, data: Any, indent: int = 2) -> None:
    """JSONファイルを保存

    Args:
        path: 保存先パス
        data: 保存するデータ
        indent: インデント幅
    """
    ensure_directory(path.parent)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=indent)


def save_text(path: Path, content: str) -> None:
    """テキストファイルを保存

    Args:
        path: 保存先パス
        content: 保存する内容
    """
    ensure_directory(path.parent)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)


def save_binary(path: Path, content: bytes) -> None:
    """バイナリファイルを保存

    Args:
        path: 保存先パス
        content: 保存する内容
    """
    ensure_directory(path.parent)
    with open(path, "wb") as f:
        f.write(content)
