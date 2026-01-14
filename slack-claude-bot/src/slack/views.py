"""Slack message view definitions."""

from typing import Optional


def build_branch_select_message(
    versions: list[str],
    current_version: Optional[str] = None,
) -> dict:
    """Build branch selection message with buttons.

    Args:
        versions: List of available version names from versions.json
        current_version: Default/current version to highlight

    Returns:
        Slack message blocks with branch selection buttons
    """
    if not versions:
        versions = ["main"]

    # Create button elements for each version
    button_elements = [
        {
            "type": "button",
            "text": {
                "type": "plain_text",
                "text": version,
            },
            "value": version,
            "action_id": f"select_branch_{version}",
            **({"style": "primary"} if version == current_version else {}),
        }
        for version in versions
    ]

    # Group buttons into rows (max 5 per row)
    button_blocks = []
    for i in range(0, len(button_elements), 5):
        button_blocks.append({
            "type": "actions",
            "elements": button_elements[i:i+5],
        })

    return {
        "blocks": [
            {
                "type": "section",
                "text": {
                    "type": "mrkdwn",
                    "text": "ブランチ（バージョン）を選択してください:",
                },
            },
            *button_blocks,
        ],
    }
