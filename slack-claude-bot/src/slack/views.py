"""Slack modal view definitions."""

from typing import Optional


def build_glow_brain_modal(
    versions: list[str],
    current_version: Optional[str] = None,
    channel_id: Optional[str] = None,
) -> dict:
    """Build modal view for /glow-brain command.

    Args:
        versions: List of available version names from versions.json
        current_version: Default/current version to pre-select
        channel_id: Channel ID where the command was invoked

    Returns:
        Slack modal view definition (Block Kit)
    """
    if not versions:
        versions = ["main"]

    # Use current_version if provided and in list, otherwise use first version
    default_version = (
        current_version if current_version and current_version in versions else versions[0]
    )

    return {
        "type": "modal",
        "callback_id": "glow_brain_modal",
        "private_metadata": channel_id or "",
        "title": {
            "type": "plain_text",
            "text": "GLOW Brain Claude",
        },
        "submit": {
            "type": "plain_text",
            "text": "送信",
        },
        "close": {
            "type": "plain_text",
            "text": "キャンセル",
        },
        "blocks": [
            {
                "type": "input",
                "block_id": "branch_block",
                "element": {
                    "type": "static_select",
                    "action_id": "branch_select",
                    "placeholder": {
                        "type": "plain_text",
                        "text": "バージョン（ブランチ）を選択",
                    },
                    "options": [
                        {
                            "text": {
                                "type": "plain_text",
                                "text": version,
                            },
                            "value": version,
                        }
                        for version in versions
                    ],
                    "initial_option": {
                        "text": {
                            "type": "plain_text",
                            "text": default_version,
                        },
                        "value": default_version,
                    },
                },
                "label": {
                    "type": "plain_text",
                    "text": "バージョン（ブランチ）",
                },
            },
            {
                "type": "input",
                "block_id": "prompt_block",
                "element": {
                    "type": "plain_text_input",
                    "action_id": "prompt_input",
                    "multiline": True,
                    "placeholder": {
                        "type": "plain_text",
                        "text": "Claudeへの指示を入力してください",
                    },
                },
                "label": {
                    "type": "plain_text",
                    "text": "プロンプト",
                },
            },
        ],
    }
