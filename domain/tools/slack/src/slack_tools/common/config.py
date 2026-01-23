"""設定管理モジュール"""

import os
from pathlib import Path

from dotenv import load_dotenv


class Config:
    """Slack Tools 共通設定管理"""

    def __init__(self):
        """環境変数から設定を読み込む"""
        load_dotenv()

        self.slack_token = os.getenv("SLACK_TOKEN")

        # glow-brainルートから固定パスを構築
        self.output_base = self._get_glow_brain_root() / "domain/raw-data/slack"

    @staticmethod
    def _get_glow_brain_root() -> Path:
        """glow-brainリポジトリのルートディレクトリを検出

        Returns:
            glow-brainルートの絶対パス

        Raises:
            RuntimeError: gitリポジトリが見つからない場合
        """
        current = Path(__file__).resolve()
        for parent in [current] + list(current.parents):
            if (parent / ".git").exists():
                return parent
        raise RuntimeError("glow-brainリポジトリのルートが見つかりません")

    def validate(self) -> None:
        """必須設定のバリデーション"""
        if not self.slack_token:
            raise ValueError(
                "SLACK_TOKEN が設定されていません。"
                ".env ファイルを作成して設定してください。"
            )

    def get_output_path(self, *parts: str) -> Path:
        """出力パスを構築する

        Args:
            *parts: パスの構成要素

        Returns:
            完全な出力パス
        """
        return self.output_base.joinpath(*parts)
