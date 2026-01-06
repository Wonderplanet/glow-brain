---
description: n8nワークフローJSON生成
argument-hint: [ワークフロー要件の詳細]
---

# n8nワークフロー生成

ユーザーの要件に基づいて、n8nにインポート可能な正確なJSON形式のワークフローを生成します。

## 引数

- `$ALL_ARGS`: ワークフロー要件の詳細な説明（例: "GitHubのissueが作成されたらSlackに通知する"）

## タスク

### 1. **要件のヒアリング**

引数が提供されていない場合、または詳細が不十分な場合は、AskUserQuestionツールを使って以下を確認してください:

- **ワークフロー名**: わかりやすい名前
- **トリガー**: どのイベントで開始するか（Webhook, Manual, Schedule, etc.）
- **処理ノード**: どのような処理を行うか（HTTP Request, Set, Code, etc.）
- **出力/アクション**: 最終的にどこに結果を送るか（Slack, Email, Database, etc.）
- **エラーハンドリング**: エラー時の処理が必要か

### 2. **n8n公式仕様の参照**

ワークフローJSONを生成する前に、必ず以下の公式仕様を確認してください:

- ノード構造: 各ノードには `id`, `name`, `type`, `typeVersion`, `position`, `parameters` が必須
- コネクション構造: `connections` オブジェクトでノード間のデータフローを定義
- データ形式: n8nは全データをオブジェクトの配列として扱い、`json` キーでラップ
- バージョン情報: 各ノードの `typeVersion` を適切に設定

**公式ドキュメント参照:**
- Workflow Export/Import: https://docs.n8n.io/workflows/export-import/
- Data Structure: https://docs.n8n.io/data/data-structure/
- Connections: https://docs.n8n.io/workflows/components/connections/
- Nodes: https://docs.n8n.io/workflows/components/nodes/

### 3. **ワークフローJSON構造の生成**

以下の厳密な構造に従ってJSONを生成してください:

```json
{
  "name": "ワークフロー名",
  "nodes": [
    {
      "parameters": {
        // ノード固有のパラメータ
      },
      "id": "一意のUUID（例: a1b2c3d4-e5f6-7g8h-9i0j-k1l2m3n4o5p6）",
      "name": "ノード表示名",
      "type": "n8n-nodes-base.ノードタイプ",
      "typeVersion": 1,
      "position": [x座標, y座標]
    }
  ],
  "connections": {
    "ソースノード名": {
      "main": [
        [
          {
            "node": "ターゲットノード名",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  },
  "settings": {
    "executionOrder": "v1"
  },
  "pinData": {},
  "staticData": null,
  "tags": [],
  "triggerCount": 0,
  "updatedAt": "2026-01-06T00:00:00.000Z",
  "versionId": "1"
}
```

### 4. **主要ノードタイプの選択**

ユーザーの要件に応じて、以下の一般的なノードタイプから適切なものを選択してください:

**トリガーノード:**
- `n8n-nodes-base.manualTrigger`: 手動トリガー
- `n8n-nodes-base.webhook`: Webhook受信
- `n8n-nodes-base.scheduleTrigger`: スケジュール実行
- `n8n-nodes-base.emailTrigger`: メール受信

**処理ノード:**
- `n8n-nodes-base.httpRequest`: HTTP APIリクエスト
- `n8n-nodes-base.set`: データセット/変換
- `n8n-nodes-base.code`: カスタムJavaScript/Python実行
- `n8n-nodes-base.if`: 条件分岐
- `n8n-nodes-base.merge`: データマージ
- `n8n-nodes-base.switch`: 複数条件分岐

**アクションノード:**
- `n8n-nodes-base.slack`: Slack通知
- `n8n-nodes-base.emailSend`: メール送信
- `n8n-nodes-base.github`: GitHub操作
- `n8n-nodes-base.googleSheets`: Googleスプレッドシート
- `n8n-nodes-base.mysql`: MySQLデータベース

### 5. **コネクションの正確な定義**

コネクションは以下のルールに従って定義してください:

- **ソースノード名をキー**として使用（nodes配列内のnameと完全一致）
- **main配列**でメインの出力を定義
- 各接続オブジェクトに `node`, `type`, `index` を含める
- ノードの実行順序を考慮した論理的な接続を作成

例:
```json
"connections": {
  "Webhook": {
    "main": [
      [
        {
          "node": "HTTP Request",
          "type": "main",
          "index": 0
        }
      ]
    ]
  },
  "HTTP Request": {
    "main": [
      [
        {
          "node": "Slack",
          "type": "main",
          "index": 0
        }
      ]
    ]
  }
}
```

### 6. **ノードパラメータの設定**

各ノードタイプに応じて、適切なパラメータを設定してください:

**Webhook例:**
```json
"parameters": {
  "httpMethod": "POST",
  "path": "webhook-path",
  "responseMode": "onReceived",
  "options": {}
}
```

**HTTP Request例:**
```json
"parameters": {
  "method": "GET",
  "url": "https://api.example.com/data",
  "authentication": "none",
  "options": {}
}
```

**Slack例:**
```json
"parameters": {
  "resource": "message",
  "operation": "post",
  "channel": "#general",
  "text": "={{ $json.message }}",
  "otherOptions": {}
}
```

### 7. **座標位置の配置**

ワークフローの視覚的な配置のため、position配列を設定してください:

- **トリガーノード**: [250, 250]から開始
- **後続ノード**: 水平方向に300ピクセル間隔（例: [550, 250], [850, 250]）
- **分岐がある場合**: 垂直方向にも150ピクセル間隔を追加

### 8. **UUID生成**

各ノードの `id` フィールドには一意のUUID v4形式の文字列を生成してください:
- 形式: `xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`
- 各ノードで異なるIDを使用
- ハイフン区切りの8-4-4-4-12文字形式

### 9. **検証とベストプラクティス**

生成したJSONが以下の基準を満たしていることを確認してください:

- [ ] すべてのノードに必須フィールド（id, name, type, typeVersion, position, parameters）が含まれている
- [ ] connections内のノード名がnodes配列のnameと完全一致している
- [ ] 少なくとも1つのトリガーノードが存在する
- [ ] すべてのノードが適切に接続されている（孤立ノードがない）
- [ ] パラメータが各ノードタイプの仕様に準拠している
- [ ] JSONが有効な形式である（カンマ、括弧の対応が正しい）

### 10. **ファイル出力**

生成したワークフローJSONを以下の形式でファイルに出力してください:

- **ファイル名**: `{ワークフロー名のスネークケース}.n8n.json`（例: `github_to_slack.n8n.json`）
- **配置場所**: カレントディレクトリまたはユーザー指定のパス
- **エンコーディング**: UTF-8
- **フォーマット**: インデント2スペースで整形

## 注意事項

### 必須事項

- **公式仕様への厳密な準拠**: n8n公式ドキュメントの構造に完全に従うこと
- **バリデーション**: 生成したJSONが有効で、n8nにインポート可能であることを確認
- **クレデンシャル**: 認証情報は含めず、プレースホルダーまたは説明のみを記載
- **バージョン互換性**: 最新のn8nバージョンと互換性のあるノードタイプとtypeVersionを使用

### エラーハンドリング

- エラーハンドリングが必要な場合は、`n8n-nodes-base.errorTrigger` ノードを追加
- HTTP Requestノードには適切なタイムアウトとリトライ設定を含める
- 重要な処理の前後に `n8n-nodes-base.set` ノードでデータ検証を挟む

### データマッピング

- n8nの式構文を使用: `={{ $json.fieldName }}`
- 前のノードのデータを参照: `={{ $node["NodeName"].json["field"] }}`
- 複数アイテムの処理: デフォルトで配列の各要素が個別に処理される

## 出力例

以下は、GitHubのissue作成時にSlack通知を送る完全なワークフロー例です:

```json
{
  "name": "GitHub Issue to Slack Notification",
  "nodes": [
    {
      "parameters": {
        "httpMethod": "POST",
        "path": "github-webhook",
        "responseMode": "onReceived",
        "options": {}
      },
      "id": "a1b2c3d4-e5f6-7g8h-9i0j-k1l2m3n4o5p6",
      "name": "Webhook",
      "type": "n8n-nodes-base.webhook",
      "typeVersion": 1,
      "position": [250, 250],
      "webhookId": "auto-generated"
    },
    {
      "parameters": {
        "conditions": {
          "string": [
            {
              "value1": "={{ $json.body.action }}",
              "value2": "opened"
            }
          ]
        }
      },
      "id": "b2c3d4e5-f6g7-8h9i-0j1k-l2m3n4o5p6q7",
      "name": "IF",
      "type": "n8n-nodes-base.if",
      "typeVersion": 1,
      "position": [550, 250]
    },
    {
      "parameters": {
        "values": {
          "string": [
            {
              "name": "issue_title",
              "value": "={{ $json.body.issue.title }}"
            },
            {
              "name": "issue_url",
              "value": "={{ $json.body.issue.html_url }}"
            },
            {
              "name": "repo_name",
              "value": "={{ $json.body.repository.full_name }}"
            }
          ]
        },
        "options": {}
      },
      "id": "c3d4e5f6-g7h8-9i0j-1k2l-m3n4o5p6q7r8",
      "name": "Set",
      "type": "n8n-nodes-base.set",
      "typeVersion": 1,
      "position": [850, 150]
    },
    {
      "parameters": {
        "resource": "message",
        "operation": "post",
        "channel": "#github-notifications",
        "text": "=新しいIssueが作成されました！\n\n📋 *{{ $json.issue_title }}*\n🔗 {{ $json.issue_url }}\n📦 リポジトリ: {{ $json.repo_name }}",
        "otherOptions": {}
      },
      "id": "d4e5f6g7-h8i9-0j1k-2l3m-n4o5p6q7r8s9",
      "name": "Slack",
      "type": "n8n-nodes-base.slack",
      "typeVersion": 1,
      "position": [1150, 150]
    }
  ],
  "connections": {
    "Webhook": {
      "main": [
        [
          {
            "node": "IF",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "IF": {
      "main": [
        [
          {
            "node": "Set",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Set": {
      "main": [
        [
          {
            "node": "Slack",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  },
  "settings": {
    "executionOrder": "v1"
  },
  "pinData": {},
  "staticData": null,
  "tags": [],
  "triggerCount": 0,
  "updatedAt": "2026-01-06T00:00:00.000Z",
  "versionId": "1"
}
```

## インポート手順

生成されたJSONファイルをn8nにインポートする方法:

1. n8nのワークフロー画面を開く
2. 右上の「...」メニューをクリック
3. 「Import from File」を選択
4. 生成された `.n8n.json` ファイルを選択
5. 必要に応じてクレデンシャルを設定
6. ワークフローをアクティブ化

## 参考リソース

- [n8n Workflow Export/Import Documentation](https://docs.n8n.io/workflows/export-import/)
- [n8n Data Structure Guide](https://docs.n8n.io/data/data-structure/)
- [n8n Connections Documentation](https://docs.n8n.io/workflows/components/connections/)
- [n8n Nodes Documentation](https://docs.n8n.io/workflows/components/nodes/)
- [n8n Workflow Examples Repository](https://github.com/Zie619/n8n-workflows)
