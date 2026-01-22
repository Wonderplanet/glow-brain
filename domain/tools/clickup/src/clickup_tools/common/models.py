"""ClickUp データモデル"""

from dataclasses import dataclass
from datetime import datetime
from typing import Optional, List, Dict, Any


@dataclass
class Attachment:
    """添付ファイル"""

    id: str
    filename: str
    url: str
    size: int
    mimetype: str
    date: Optional[datetime] = None

    @classmethod
    def from_api(cls, data: Dict[str, Any]) -> "Attachment":
        """API レスポンスから変換"""
        return cls(
            id=data["id"],
            filename=data["title"],
            url=data["url"],
            size=data.get("size", 0),
            mimetype=data.get("mimetype", ""),
            date=datetime.fromtimestamp(int(data["date"]) / 1000)
            if data.get("date")
            else None,
        )


@dataclass
class Comment:
    """コメント"""

    id: str
    text: str
    user_name: str
    date: datetime

    @classmethod
    def from_api(cls, data: Dict[str, Any]) -> "Comment":
        """API レスポンスから変換"""
        return cls(
            id=data["id"],
            text=data.get("comment_text", ""),
            user_name=data.get("user", {}).get("username", "Unknown"),
            date=datetime.fromtimestamp(int(data["date"]) / 1000),
        )


@dataclass
class CustomField:
    """カスタムフィールド"""

    name: str
    value: Any
    type: str

    @classmethod
    def from_api(cls, data: Dict[str, Any]) -> "CustomField":
        """API レスポンスから変換"""
        field_type = data.get("type", "")
        value = data.get("value")

        # 型に応じて値を変換
        if field_type == "drop_down" and isinstance(value, dict):
            value = value.get("name", "")
        elif field_type == "date" and value:
            value = datetime.fromtimestamp(int(value) / 1000)

        return cls(name=data.get("name", ""), value=value, type=field_type)


@dataclass
class Task:
    """タスク（チケット）"""

    id: str
    name: str
    description: Optional[str]
    status: str
    priority: Optional[str]
    due_date: Optional[datetime]
    created_date: datetime
    updated_date: Optional[datetime]
    url: str
    creator_name: str
    assignees: List[str]
    tags: List[str]
    attachments: List[Attachment]
    custom_fields: List[CustomField]
    parent: Optional[str] = None

    @classmethod
    def from_api(cls, data: Dict[str, Any]) -> "Task":
        """API レスポンスから変換"""
        return cls(
            id=data["id"],
            name=data["name"],
            description=data.get("description"),
            status=data["status"]["status"],
            priority=data.get("priority", {}).get("priority") if data.get("priority") else None,
            due_date=datetime.fromtimestamp(int(data["due_date"]) / 1000)
            if data.get("due_date")
            else None,
            created_date=datetime.fromtimestamp(int(data["date_created"]) / 1000),
            updated_date=datetime.fromtimestamp(int(data["date_updated"]) / 1000)
            if data.get("date_updated")
            else None,
            url=data["url"],
            creator_name=data.get("creator", {}).get("username", "Unknown"),
            assignees=[
                assignee.get("username", "Unknown")
                for assignee in data.get("assignees", [])
            ],
            tags=[tag.get("name", "") for tag in data.get("tags", [])],
            attachments=[
                Attachment.from_api(att) for att in data.get("attachments", [])
            ],
            custom_fields=[
                CustomField.from_api(field)
                for field in data.get("custom_fields", [])
            ],
            parent=data.get("parent"),
        )


@dataclass
class ListInfo:
    """リスト情報"""

    id: str
    name: str
    space_name: str
    folder_name: Optional[str] = None

    @classmethod
    def from_api(cls, data: Dict[str, Any]) -> "ListInfo":
        """API レスポンスから変換"""
        folder = data.get("folder", {})
        return cls(
            id=data["id"],
            name=data["name"],
            space_name=data.get("space", {}).get("name", "Unknown"),
            folder_name=folder.get("name") if folder else None,
        )


@dataclass
class UrlInfo:
    """URL 情報"""

    type: str  # "list", "task", "space", etc.
    id: str
    raw_url: str
