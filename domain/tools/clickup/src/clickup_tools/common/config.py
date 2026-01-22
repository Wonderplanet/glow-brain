"""設定管理モジュール"""

import os
from pathlib import Path
from dotenv import load_dotenv


class Config:
    """ClickUp Tools 共通設定管理"""

    def __init__(self):
        """環境変数から設定を読み込む"""
        load_dotenv()

        self.api_key = os.getenv("CLICKUP_API_KEY")
        self.output_base = Path(
            os.getenv("CLICKUP_OUTPUT_BASE", "domain/raw-data/clickup")
        )

    def validate(self) -> None:
        """必須設定のバリデーション"""
        if not self.api_key:
            raise ValueError(
                "CLICKUP_API_KEY が設定されていません。"
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
