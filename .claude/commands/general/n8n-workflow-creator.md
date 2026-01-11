---
description: n8nワークフローJSONを作成してエクスポート
argument-hint: [ワークフロー名] [説明]
---

# n8n ワークフロー作成コマンド

n8nのワークフローをJSONファイルとして作成し、そのままインポートできる形式で出力します。
公式ドキュメントから最新のノード情報を取得し、存在しないノードや無効な設定を避けます。

## 引数

- `$1`: ワークフロー名（例: "My Automation Workflow"）
- `$2`: ワークフローの説明（例: "Slackへの通知を自動化"）

## 公式ドキュメントリンク

**必ず最新の情報を確認してください:**

- **n8nワークフローエクスポート/インポート**: https://docs.n8n.io/workflows/export-import/
- **n8nノードタイプ一覧**: https://docs.n8n.io/integrations/builtin/node-types/
- **n8nコアノード一覧**: https://docs.n8n.io/integrations/builtin/core-nodes/
- **n8nデータ構造**: https://docs.n8n.io/data/data-structure/
- **n8nノード標準パラメータ**: https://docs.n8n.io/integrations/creating-nodes/build/reference/node-base-files/standard-parameters/

## タスク

### 1. ワークフロー要件のヒアリング

ユーザーから以下の情報を収集してください：

- **トリガー**: どのようなきっかけでワークフローを開始するか
  - 例: Webhook受信、スケジュール実行、手動実行など
- **処理内容**: どのようなデータ処理を行うか
  - 例: データ変換、API呼び出し、条件分岐など
- **出力先**: 処理結果をどこに送るか
  - 例: Slack通知、データベース保存、メール送信など

AskUserQuestionツールを使って対話的に情報を収集してください。

### 2. 使用するノードの調査と検証

**重要: 必ず公式ドキュメントから最新情報を取得してください**

各ノードについて以下を確認します:

1. **ノードタイプ名の正確性**
   - WebFetchツールで https://docs.n8n.io/integrations/builtin/core-nodes/ を取得
   - 使用予定のノードの正確な名前（`n8n-nodes-base.XXX`形式）を確認

2. **ノードのパラメータ仕様**
   - 各ノードの公式ドキュメントページをWebFetchで取得
   - 利用可能なパラメータ、オプション、必須フィールドを確認
   - 例: `n8n-nodes-base.httprequest` の場合は https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.httprequest/

3. **バージョン互換性**
   - 現在の安定版は 2026-01-06 リリース版
   - ノードの機能が最新版で利用可能か確認

### 3. ワークフローJSON構造の作成

n8nの標準的なワークフロー構造に従ってJSONを作成します:

```json
{
  "name": "$1",
  "nodes": [
    {
      "parameters": {},
      "id": "一意のUUID",
      "name": "ノード名",
      "type": "n8n-nodes-base.XXX",
      "typeVersion": 1,
      "position": [x, y]
    }
  ],
  "connections": {
    "ノード名1": {
      "main": [
        [
          {
            "node": "ノード名2",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  },
  "pinData": {},
  "settings": {
    "executionOrder": "v1"
  },
  "staticData": null,
  "tags": [],
  "triggerCount": 1,
  "updatedAt": "ISO 8601形式のタイムスタンプ",
  "versionId": "一意のUUID"
}
```

**重要な注意点:**

- **ノードID**: 各ノードに一意のUUIDを割り当て（crypto.randomUUID()形式）
- **ノード位置**: 視覚的に見やすい配置（x座標は200間隔、y座標は300など）
- **接続**: ノード間のデータフローを正確に定義
- **typeVersion**: 各ノードの適切なバージョンを指定（通常は1）
- **タイムスタンプ**: 現在時刻をISO 8601形式で記載

### 4. ノードパラメータの詳細設定

各ノードタイプに応じて、**公式ドキュメントで確認した正確なパラメータ**を設定します:

#### トリガーノードの例

```json
{
  "parameters": {
    "httpMethod": "POST",
    "path": "webhook-path",
    "responseMode": "onReceived",
    "options": {}
  },
  "type": "n8n-nodes-base.webhook",
  "name": "Webhook"
}
```

#### HTTP Requestノードの例

```json
{
  "parameters": {
    "method": "POST",
    "url": "https://api.example.com/endpoint",
    "authentication": "none",
    "options": {}
  },
  "type": "n8n-nodes-base.httprequest",
  "name": "HTTP Request"
}
```

**検証ステップ:**

1. 使用する各ノードの公式ドキュメントをWebFetchで取得
2. parametersフィールドで使用可能なオプションをリストアップ
3. 存在しないパラメータや選択肢を使用していないか確認
4. 必須パラメータが全て設定されているか確認

### 5. 完全性チェックリスト

作成したJSONについて以下を確認してください:

- [ ] すべてのノードタイプ名が公式ドキュメントに存在する
- [ ] 各ノードのパラメータが公式ドキュメントの仕様に一致
- [ ] ノード間の接続が正しく定義されている
- [ ] 必須フィールド（id, name, type, position）が全ノードに存在
- [ ] JSON構文が正しい（カンマ、括弧、引用符など）
- [ ] トリガーノードが存在する（triggerCountが1以上）
- [ ] タイムスタンプが有効なISO 8601形式
- [ ] UUIDが正しい形式（xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx）

### 6. JSONファイルの出力

Writeツールを使って、以下のパスにワークフローJSONを保存します:

```
n8n-workflows/[ワークフロー名をケバブケースに変換].json
```

例: "My Automation Workflow" → `n8n-workflows/my-automation-workflow.json`

### 7. インポート手順の説明

ユーザーに以下の手順を提示してください:

1. **n8nでのインポート方法:**
   ```
   n8n画面右上のメニュー（3点ドット） → Import from File → 作成したJSONファイルを選択
   ```

2. **認証情報の設定:**
   ```
   インポート後、各ノードの認証情報を設定してください。
   JSONファイルには認証情報は含まれていません（セキュリティのため）。
   ```

3. **動作確認:**
   ```
   - 各ノードの設定を確認
   - テストデータで実行
   - 接続が正しく動作するか確認
   ```

## 検証プロセスの徹底

### Phase 1: ノード存在確認

```javascript
// 各ノードについて以下を実行:
1. WebFetch で https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.[ノード名]/ を取得
2. ページが存在するか確認（404エラーでないか）
3. ノードタイプ名が正確か確認
```

### Phase 2: パラメータ検証

```javascript
// 各ノードのパラメータについて:
1. 公式ドキュメントから利用可能なパラメータリストを抽出
2. 使用予定のパラメータが全てリストに含まれているか確認
3. enum型の場合、選択肢が有効か確認
```

### Phase 3: JSON構造検証

```javascript
// 最終チェック:
1. JSON構文の妥当性
2. 必須フィールドの存在
3. 参照整合性（connections内のノード名が実際に存在するか）
```

## エラー回避のためのベストプラクティス

1. **ノード名の大文字小文字を正確に**
   - ❌ `n8n-nodes-base.HTTPRequest`
   - ✅ `n8n-nodes-base.httprequest`

2. **存在しないパラメータを使用しない**
   - ❌ `"authType": "bearer"` （正しくは `"authentication": "genericCredentialType"`）
   - ✅ 公式ドキュメントで確認したパラメータのみ使用

3. **typeVersionを適切に設定**
   - ノードが複数バージョンある場合は最新版を使用
   - 通常は `"typeVersion": 1` で問題なし

4. **UUIDの形式を正確に**
   - ❌ `"id": "node123"`
   - ✅ `"id": "550e8400-e29b-41d4-a716-446655440000"`

5. **接続定義の正確性**
   - ノード名のスペルミスに注意
   - 接続インデックスが正しいか確認

## 出力形式

最終的に以下の構造で出力してください:

```
作成完了: n8n-workflows/[ワークフロー名].json

【ワークフロー概要】
- 名前: $1
- 説明: $2
- ノード数: X個
- トリガー: [トリガーノード名]

【使用ノード一覧】
1. [ノード名] (n8n-nodes-base.XXX)
   - 役割: [説明]
   - 公式ドキュメント: https://docs.n8n.io/...

【インポート手順】
1. n8n画面右上のメニュー → Import from File
2. 作成したJSONファイルを選択
3. 各ノードの認証情報を設定
4. テスト実行で動作確認

【注意事項】
- 認証情報は別途設定が必要です
- 初回実行前に各ノードの設定を確認してください
- バージョン: n8n 安定版 (2026-01-06以降)
```

## 重要な注意事項

1. **常に公式ドキュメントを参照**
   - キャッシュや古い情報に頼らない
   - WebFetchで最新情報を取得

2. **推測で記述しない**
   - ノード名、パラメータ名、選択肢は全て確認済みのもののみ使用
   - 不明な点はユーザーに質問

3. **段階的に検証**
   - ノード追加ごとに公式ドキュメントで確認
   - 一度に全てを作らず、検証しながら進める

4. **セキュリティ配慮**
   - 認証情報は含めない
   - 機密情報をハードコードしない

## 参考リソース

### 主要ドキュメント
- [n8n Workflows Export/Import](https://docs.n8n.io/workflows/export-import/)
- [n8n Node Types](https://docs.n8n.io/integrations/builtin/node-types/)
- [n8n Core Nodes](https://docs.n8n.io/integrations/builtin/core-nodes/)
- [n8n Data Structure](https://docs.n8n.io/data/data-structure/)

### コミュニティリソース
- [n8n Community Forum](https://community.n8n.io/)
- [n8n Workflow Templates](https://n8n.io/workflows/)
- [n8n GitHub Repository](https://github.com/n8n-io/n8n)

### ベストプラクティス記事
- [N8N Import Workflow JSON: Complete Guide + File Format Examples 2025](https://latenode.com/blog/low-code-no-code-platforms/n8n-setup-workflows-self-hosting-templates/n8n-import-workflow-json-complete-guide-file-format-examples-2025)
- [N8N Export/Import Workflows: Complete JSON Guide](https://latenode.com/blog/low-code-no-code-platforms/n8n-setup-workflows-self-hosting-templates/n8n-export-import-workflows-complete-json-guide-troubleshooting-common-failures-2025)
