"""ファイル操作ユーティリティ"""

import re
from pathlib import Path


def sanitize_filename(name: str, max_length: int = 100) -> str:
    """ファイル名に使えない文字を置換する

    Args:
        name: 元のファイル名
        max_length: 最大長

    Returns:
        サニタイズされたファイル名
    """
    # 使えない文字を置換
    sanitized = re.sub(r'[<>:"/\\|?*]', "_", name)

    # 連続するスペースやアンダースコアを1つに
    sanitized = re.sub(r"[ _]+", "_", sanitized)

    # 前後の空白・アンダースコアを削除
    sanitized = sanitized.strip("_ ")

    # 最大長でカット
    if len(sanitized) > max_length:
        sanitized = sanitized[:max_length].rstrip("_")

    # 空の場合はデフォルト名
    if not sanitized:
        sanitized = "unnamed"

    return sanitized


def ensure_directory(path: Path) -> Path:
    """ディレクトリが存在しない場合は作成する

    Args:
        path: ディレクトリパス

    Returns:
        作成されたディレクトリパス
    """
    path.mkdir(parents=True, exist_ok=True)
    return path


def save_file(content: bytes, path: Path) -> None:
    """バイナリファイルを保存する

    Args:
        content: ファイル内容
        path: 保存先パス
    """
    # 親ディレクトリを作成
    ensure_directory(path.parent)

    # ファイルを保存
    path.write_bytes(content)


def save_text(content: str, path: Path, encoding: str = "utf-8") -> None:
    """テキストファイルを保存する

    Args:
        content: ファイル内容
        path: 保存先パス
        encoding: 文字エンコーディング
    """
    # 親ディレクトリを作成
    ensure_directory(path.parent)

    # ファイルを保存
    path.write_text(content, encoding=encoding)
