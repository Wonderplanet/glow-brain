# 動作確認手順

## ローカル環境での確認

ローカル環境はAthena対応環境ではないため、通常のDBクエリのみが実行されます。

### 確認ポイント

1. **ページが正常に表示されること**
2. **フィルターが正常に動作すること**
3. **ソートが正常に動作すること**
4. **CSV出力が正常に動作すること**

```bash
# ローカルでadminを起動
docker compose up -d
```

ブラウザで管理画面にアクセスし、実装したログページを確認します。

## develop環境での確認

develop環境ではAthenaクエリが有効です。

### 前提条件

1. PRがマージされていること
2. AthenaテーブルがAWSコンソールで作成済みであること

**注意**: develop環境ではS3へのデータエクスポート（日次バッチ）を実行していないため、Athenaクエリでデータが取得できるとは限りません。

### Athenaクエリが使用される条件

以下の条件を満たすとAthenaクエリに切り替わります：

- 環境: `develop` または `production`
- フィルター: `created_at_range` で日付範囲が指定されている
- 日付: 開始日が現在から **30日以上前**

### 確認手順

1. develop環境の管理画面にアクセス
2. 実装したログページを開く
3. 日付範囲フィルターで **31日以上前〜10日間以内の期間** を指定
4. 「検索」ボタンをクリック
5. **AWSコンソールのAthenaクエリ履歴**でクエリが実行されていることを確認

### Athenaクエリ使用の確認方法

develop環境ではデータが存在しない可能性があるため、**Athenaコンソール上でクエリが実行されていることを確認**してください：

1. **AWSコンソール**: [Athenaクエリ履歴](https://ap-northeast-1.console.aws.amazon.com/athena/home?region=ap-northeast-1#/query-editor/history)で実行されたクエリを確認
2. **ログで確認**: `storage/logs/laravel.log` でAthena関連のログを確認（補助的）

## production環境での確認

本番環境ではS3へのデータエクスポート（日次バッチ）が稼働しています。

### 確認手順

develop環境と同様に、[Athenaクエリ履歴](https://ap-northeast-1.console.aws.amazon.com/athena/home?region=ap-northeast-1#/query-editor/history)でクエリが実行されていることを確認してください。

**注意**: 実際にAthenaクエリでデータが取得できるのは、リリース後30日以上経過してからになります（30日以内のデータはDBから取得されるため）。

### 注意事項

- 本番データに影響を与えないよう、参照のみの操作を行う
- 大量データのクエリは避ける（日付範囲を絞る）

## エラー発生時の対処

### Athenaクエリタイムアウト

```
Athena Query Timeout
```

→ 日付範囲を狭めて再度クエリを実行

### テーブルが見つからない

```
Table 'glow_develop_user_action_logs.log_xxx' does not exist
```

→ AWSコンソールでAthenaテーブルを作成する必要があります

### データが表示されない

1. S3にデータがエクスポートされているか確認
2. パーティション（dt）の日付範囲が正しいか確認
3. クエリの条件が正しいか確認

## チェックリスト

### ローカル環境

- [ ] ページが正常に表示される
- [ ] 日付範囲フィルターが動作する
- [ ] ソートが動作する
- [ ] CSV出力が動作する

### develop環境

- [ ] AthenaテーブルSQLがマージされている
- [ ] AWSコンソールでテーブルが作成されている
- [ ] 30日以上前の日付範囲を指定して検索実行
- [ ] [Athenaクエリ履歴](https://ap-northeast-1.console.aws.amazon.com/athena/home?region=ap-northeast-1#/query-editor/history)でクエリが実行されていることを確認

### production環境

- [ ] AthenaテーブルSQLがマージされている
- [ ] AWSコンソールでテーブルが作成されている
- [ ] 30日以上前の日付範囲を指定して検索実行
- [ ] [Athenaクエリ履歴](https://ap-northeast-1.console.aws.amazon.com/athena/home?region=ap-northeast-1#/query-editor/history)でクエリが実行されていることを確認
- [ ] （リリース30日後以降）実際にデータが取得できることを確認
