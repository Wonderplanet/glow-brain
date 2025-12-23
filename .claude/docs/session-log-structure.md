# Claude Code セッションログ (JSONL) データ構造仕様

このドキュメントは、Claude Codeのセッションログファイル（`~/.claude/projects/<project-key>/*.jsonl`）のデータ構造を詳細に解説します。エクスポートスクリプトの開発や調整に活用してください。

## 概要

- **ファイル形式**: JSONL (JSON Lines) - 1行に1つのJSONオブジェクト
- **保存場所**: `~/.claude/projects/<project-key>/<session-id>.jsonl`
- **文字コード**: UTF-8
- **用途**: セッション中の全てのメッセージ、ツール実行、システムイベントを記録

## メッセージタイプの統計（サンプルセッション）

```
301件  assistant           - Assistantの応答
123件  user               - ユーザーメッセージ（プロンプト + ツール結果）
 15件  file-history-snapshot - ファイル履歴スナップショット
  2件  summary            - セッションサマリー
  1件  system             - システムメッセージ
```

---

## 共通フィールド

全てのメッセージタイプで共通するフィールド：

| フィールド | 型 | 説明 |
|-----------|-----|------|
| `type` | string | メッセージタイプ (`user`, `assistant`, `system`, `summary`, `file-history-snapshot`) |
| `timestamp` | string (ISO 8601) | メッセージのタイムスタンプ (`2025-12-23T12:36:04.584Z`) |
| `uuid` | string | メッセージの一意識別子 |
| `sessionId` | string | セッションID |
| `version` | string | Claude Codeのバージョン (`2.0.76`) |
| `cwd` | string | 作業ディレクトリの絶対パス |
| `gitBranch` | string | 現在のGitブランチ名 |
| `slug` | string | セッションのスラッグ名 |
| `parentUuid` | string \| null | 親メッセージのUUID（会話のスレッド構造を表す） |
| `isSidechain` | boolean | サイドチェーンメッセージかどうか |
| `userType` | string | ユーザータイプ (`external`) |

---

## メッセージタイプ別詳細

### 1. `user` (ユーザーメッセージ)

ユーザーからのプロンプトとツール実行結果の両方を含みます。

#### 1.1 ユーザープロンプト（実際のユーザー入力）

**識別条件**:
- `type === "user"`
- `parentUuid === null`
- `message.content` が文字列型

**サンプル構造**:
```json
{
  "type": "user",
  "parentUuid": null,
  "timestamp": "2025-12-23T12:36:04.584Z",
  "uuid": "404b1f87-2ed9-409b-8d0e-53ff169f9769",
  "sessionId": "2eaa95c4-cc9d-42a4-892a-03d212691bf2",
  "version": "2.0.76",
  "cwd": "/Users/junki.mizutani/Documents/workspace/glow/glow-brain",
  "gitBranch": "main",
  "slug": "cozy-fluttering-knuth",
  "isSidechain": false,
  "userType": "external",
  "message": {
    "role": "user",
    "content": "下記のスクリプトを@.claude/scriptsに調整した形で作ってほしい。..."
  },
  "thinkingMetadata": {
    "level": "high",
    "disabled": false,
    "triggers": []
  },
  "todos": []
}
```

**特殊フィールド**:
- `thinkingMetadata`: 思考モードの設定
- `todos`: Todoリスト（あれば）

#### 1.2 ツール実行結果

**識別条件**:
- `type === "user"`
- `parentUuid !== null`
- `message.content` が配列型で `tool_result` を含む

**サンプル構造**:
```json
{
  "type": "user",
  "parentUuid": "0a208704-501f-4deb-8abf-b7194cddb076",
  "timestamp": "2025-12-23T12:37:37.190Z",
  "uuid": "941f8b97-1bfe-448a-88a3-8f103c983068",
  "message": {
    "role": "user",
    "content": [
      {
        "type": "tool_result",
        "tool_use_id": "toolu_01234567890",
        "content": "ツール実行の結果テキスト..."
      }
    ]
  },
  "toolUseResult": {
    // ツール固有の結果データ（ツールによって異なる）
    "questions": [...],  // AskUserQuestion の場合
    "answers": {...},
    "stdout": "...",     // Bash の場合
    "file": {...}        // Read/Write の場合
  }
}
```

**message.content 配列の要素**:
- `type`: "tool_result"
- `tool_use_id`: 対応する tool_use のID
- `content`: ツール実行結果（文字列またはオブジェクト）
- `is_error`: エラーの場合 `true`

#### 1.3 要約コンテキスト（Compact Summary）

**識別条件**:
- `type === "user"`
- `isCompactSummary === true`

**サンプル構造**:
```json
{
  "type": "user",
  "parentUuid": "7e2e9dd1-af20-426a-8863-d1524560a40f",
  "timestamp": "2025-12-23T14:26:36.158Z",
  "uuid": "c7b8fec4-d190-4c17-93d6-c7bee52d77b4",
  "isCompactSummary": true,
  "isVisibleInTranscriptOnly": true,
  "message": {
    "role": "user",
    "content": "This session is being continued from a previous conversation that ran out of context. The conversation is summarized below:..."
  }
}
```

**特殊フラグ**:
- `isCompactSummary`: 要約コンテキストであることを示す
- `isVisibleInTranscriptOnly`: トランスクリプトにのみ表示（ユーザーには見えない）

**重要**: エクスポート時は**表示しない**べきメッセージです。

---

### 2. `assistant` (Assistantの応答)

Assistantからのテキスト応答とツール使用リクエストを含みます。

#### 2.1 テキスト応答

**識別条件**:
- `type === "assistant"`
- `message.content` に `type: "text"` の要素を含む

**サンプル構造**:
```json
{
  "type": "assistant",
  "parentUuid": "404b1f87-2ed9-409b-8d0e-53ff169f9769",
  "timestamp": "2025-12-23T12:36:11.244Z",
  "uuid": "0a208704-501f-4deb-8abf-b7194cddb076",
  "message": {
    "role": "assistant",
    "content": [
      {
        "type": "text",
        "text": "承知しました。スクリプトを調整して作成します..."
      }
    ],
    "usage": {
      "input_tokens": 5234,
      "output_tokens": 512,
      "cache_read_input_tokens": 2048
    }
  },
  "requestId": "req_01234567890"
}
```

**message.content 配列の要素**:
- `type`: "text"
- `text`: Assistantの応答テキスト

**usage オブジェクト**:
- `input_tokens`: 入力トークン数
- `output_tokens`: 出力トークン数
- `cache_read_input_tokens`: キャッシュから読み込んだトークン数

#### 2.2 ツール使用リクエスト

**識別条件**:
- `type === "assistant"`
- `message.content` に `type: "tool_use"` の要素を含む

**サンプル構造**:
```json
{
  "type": "assistant",
  "message": {
    "content": [
      {
        "type": "text",
        "text": "まず、既存のスクリプトを読み込みます。"
      },
      {
        "type": "tool_use",
        "id": "toolu_01234567890",
        "name": "Read",
        "input": {
          "file_path": "/path/to/file.js"
        }
      }
    ]
  }
}
```

**tool_use オブジェクト**:
- `id`: ツール使用の一意識別子
- `name`: ツール名 (`Read`, `Write`, `Edit`, `Bash`, `Grep`, `Glob`, `Task`, etc.)
- `input`: ツールへの入力パラメータ（ツールによって異なる）

---

### 3. `system` (システムメッセージ)

システムイベントを記録します。

**サンプル構造**:
```json
{
  "type": "system",
  "subtype": "compact_boundary",
  "parentUuid": null,
  "logicalParentUuid": "531a87a4-df20-4d80-ae02-cd71bc6d06dd",
  "timestamp": "2025-12-23T14:26:36.158Z",
  "uuid": "7e2e9dd1-af20-426a-8863-d1524560a40f",
  "content": "Conversation compacted",
  "level": "info",
  "isMeta": false,
  "compactMetadata": {
    "trigger": "auto",
    "preTokens": 155012
  }
}
```

**system メッセージの種類** (`subtype`):
- `compact_boundary`: 会話の要約境界マーカー

**特殊フィールド**:
- `logicalParentUuid`: 論理的な親メッセージ（要約前の最後のメッセージ）
- `compactMetadata`: 要約のメタデータ
  - `trigger`: トリガー (`auto`, `manual`)
  - `preTokens`: 要約前のトークン数

---

### 4. `summary` (セッションサマリー)

セッション全体のサマリー情報。

**サンプル構造**:
```json
{
  "type": "summary",
  "summary": "Claude-code subagent creation command implementation",
  "leafUuid": "02ae7c66-3f2f-45fa-a053-ed03ff1419d6"
}
```

**フィールド**:
- `summary`: セッションの要約テキスト
- `leafUuid`: セッションの最後のメッセージUUID

---

### 5. `file-history-snapshot` (ファイル履歴スナップショット)

特定時点でのファイル状態のスナップショット。

**サンプル構造**:
```json
{
  "type": "file-history-snapshot",
  "messageId": "404b1f87-2ed9-409b-8d0e-53ff169f9769",
  "snapshot": {
    "messageId": "404b1f87-2ed9-409b-8d0e-53ff169f9769",
    "trackedFileBackups": {},
    "timestamp": "2025-12-23T12:36:04.593Z"
  },
  "isSnapshotUpdate": false
}
```

**フィールド**:
- `messageId`: 対応するメッセージID
- `snapshot.trackedFileBackups`: バックアップされたファイルの内容
- `isSnapshotUpdate`: スナップショット更新かどうか

---

## メッセージ分類のベストプラクティス

### エクスポート時の判定ロジック

```javascript
function classifyMessage(msg) {
  // 1. Assistant メッセージ
  if (msg.type === 'assistant') {
    return {
      category: 'assistant',
      isToolUse: msg.message?.content?.some(item => item.type === 'tool_use') || false
    };
  }

  // 2. User メッセージ
  if (msg.type === 'user') {
    // 2.1 要約コンテキストはスキップ
    if (msg.isCompactSummary === true) {
      return { category: 'compact_summary' };
    }

    const msgContent = typeof msg.message === 'string'
      ? msg.message
      : msg.message?.content;

    // 2.2 ツール実行結果
    if (Array.isArray(msgContent)) {
      const hasToolResult = msgContent.some(item => item.type === 'tool_result');
      if (hasToolResult) {
        return { category: 'tool_result' };
      }
    }

    // 2.3 実際のユーザープロンプト
    return {
      category: 'user_prompt',
      hasSystemReminder: typeof msgContent === 'string'
        && msgContent.includes('<system-reminder>')
    };
  }

  // 3. その他のメッセージタイプ
  return { category: 'other' };
}
```

### エクスポート表示の推奨事項

| カテゴリ | 表示方法 | 備考 |
|---------|---------|------|
| `user_prompt` | **👤 User セクション** として表示 | system-reminder は別途抽出して折りたたみ表示 |
| `assistant` (text) | **🤖 Assistant セクション** として表示 | テキスト応答を表示 |
| `assistant` (tool_use) | ツール実行セクションにグループ化 | 次の tool_result とセットで表示 |
| `tool_result` | **🔧 Tool Execution セクション** として表示 | tool_use とセットで表示、長い結果は折りたたみ |
| `compact_summary` | **表示しない** | 要約コンテキストはユーザーに見せない |
| `system` | **表示しない** または補足情報として | セッション境界マーカーなど |
| `summary` | セッション冒頭のサマリーとして表示（任意） | セッション全体の概要 |
| `file-history-snapshot` | **表示しない** | 内部的なバックアップ情報 |

---

## 特殊フラグ一覧

| フラグ | 型 | 説明 | 用途 |
|-------|-----|------|------|
| `isCompactSummary` | boolean | 要約コンテキストメッセージ | エクスポート時は**除外**すべき |
| `isVisibleInTranscriptOnly` | boolean | トランスクリプトにのみ表示 | ユーザーには見えないコンテキスト |
| `isMeta` | boolean | メタ情報メッセージ | システム内部情報 |
| `isSidechain` | boolean | サイドチェーンメッセージ | 並行処理されたメッセージ |

---

## メッセージスレッド構造

### parentUuid による関連付け

メッセージは `parentUuid` フィールドで親子関係を持ちます：

```
[User Prompt] (parentUuid: null)
  ├─ [Assistant Response] (parentUuid: <User Prompt UUID>)
  │   └─ [Tool Use]
  │       └─ [Tool Result] (parentUuid: <Assistant UUID>)
  │           └─ [Assistant Response] (parentUuid: <Tool Result UUID>)
  ├─ [User Prompt] (parentUuid: <previous message UUID>)
  └─ ...
```

**注意点**:
- **最初のユーザープロンプト**: `parentUuid === null`
- **途中のユーザープロンプト**: `parentUuid !== null` （前のメッセージを指す）
- ❌ **間違った判定**: `parentUuid === null` だけでユーザープロンプトを判定すると、途中のユーザーメッセージを見逃す

---

## よくある間違いと対策

### 1. ❌ parentUuid だけでユーザープロンプトを判定

```javascript
// 間違い: 最初のメッセージしか検出できない
if (msg.type === 'user' && msg.parentUuid === null) {
  return 'user_prompt';
}
```

**正しい方法**: message.content の型と内容で判定

```javascript
// 正しい: 全てのユーザープロンプトを検出
if (msg.type === 'user') {
  if (msg.isCompactSummary) return 'compact_summary';

  const content = msg.message?.content;
  if (Array.isArray(content) && content.some(item => item.type === 'tool_result')) {
    return 'tool_result';
  }

  return 'user_prompt';
}
```

### 2. ❌ 要約コンテキストを表示してしまう

```javascript
// 間違い: isCompactSummary をチェックしていない
if (msg.type === 'user') {
  markdown += formatUserMessage(msg);
}
```

**正しい方法**: isCompactSummary をチェック

```javascript
// 正しい: 要約コンテキストはスキップ
if (msg.type === 'user' && !msg.isCompactSummary) {
  markdown += formatUserMessage(msg);
}
```

### 3. ❌ tool_use と tool_result を別々に表示

**正しい方法**: グループ化して表示

```javascript
function groupToolResults(messages) {
  const grouped = [];
  let currentGroup = null;

  messages.forEach(msg => {
    const classification = classifyMessage(msg);

    if (classification.category === 'assistant' && classification.isToolUse) {
      currentGroup = {
        assistantMsg: msg,
        toolResults: [],
        timestamp: msg.timestamp
      };
    } else if (classification.category === 'tool_result' && currentGroup) {
      currentGroup.toolResults.push(msg);
    } else {
      if (currentGroup && currentGroup.toolResults.length > 0) {
        grouped.push({ type: 'tool_group', data: currentGroup });
        currentGroup = null;
      }
      if (classification.category !== 'compact_summary') {
        grouped.push({ type: 'single', data: msg });
      }
    }
  });

  return grouped;
}
```

---

## 参考情報

### プロジェクトキーの生成方法

```javascript
// cwd: /Users/junki.mizutani/Documents/workspace/glow/glow-brain
// → projectKey: -Users-junki-mizutani-Documents-workspace-glow-glow-brain

const projectKey = '-' + cwd.substring(1).replace(/[/_.]/g, '-');
```

### セッションログファイルの場所

```
~/.claude/projects/<project-key>/<session-id>.jsonl
~/.claude/projects/<project-key>/agent-<agent-id>.jsonl  // エージェントのログ
```

### ツール名とアイコンの対応

| ツール名 | アイコン | 説明 |
|---------|---------|------|
| Read | 📖 | ファイル読み込み |
| Write | 📝 | ファイル書き込み |
| Edit | ✏️ | ファイル編集 |
| Bash | ⚙️ | シェルコマンド実行 |
| Grep | 🔍 | コード検索 |
| Glob | 📁 | ファイルパターン検索 |
| Task | 🤖 | サブエージェント起動 |
| WebFetch | 🌐 | Web取得 |
| WebSearch | 🔎 | Web検索 |
| LSP | 🔧 | 言語サーバープロトコル |
| AskUserQuestion | ❓ | ユーザーへの質問 |

---

## まとめ

このドキュメントは、Claude Codeセッションログの完全なデータ構造仕様です。エクスポートスクリプトを開発・調整する際は、以下の点に注意してください：

1. ✅ **message.content の型**で判定する（parentUuid だけに頼らない）
2. ✅ **isCompactSummary** をチェックして要約コンテキストを除外
3. ✅ **tool_use と tool_result** をグループ化して表示
4. ✅ **system-reminder** は抽出して折りたたみ表示
5. ✅ **メッセージタイプ**に応じた適切なフォーマットを適用

これらのベストプラクティスに従うことで、正確で読みやすいセッションログエクスポートが可能になります。
