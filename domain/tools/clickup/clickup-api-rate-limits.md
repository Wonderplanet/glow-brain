# ClickUp API レート制限ガイド

## 概要

ClickUp APIは、ワークスペースのプランに応じたレート制限を設定しています。制限は**トークン単位**で適用され、個人トークンとOAuthトークンの両方が対象となります。

このドキュメントでは、ClickUp APIのレート制限の詳細と、制限を効果的に管理するためのベストプラクティスを説明します。

---

## レート制限値（プラン別）

ClickUp APIのレート制限は、ワークスペースのプランによって異なります。

| ワークスペースプラン | 制限（リクエスト数/分） |
|---------------------|----------------------|
| Free Forever | 100 |
| Unlimited | 100 |
| Business | 100 |
| Business Plus | 1,000 |
| Enterprise | 10,000 |

### 重要な注意点

- レート制限は**トークン単位**で適用されます
- 個人トークン（Personal API Token）とOAuthトークンの両方に適用されます
- 制限は1分間のローリングウィンドウで計算されます

---

## レスポンスヘッダー

ClickUp APIは、すべてのレスポンスにレート制限に関する情報をヘッダーとして含めています。これらのヘッダーを監視することで、制限に達する前に適切な対処が可能です。

| ヘッダー名 | 説明 |
|-----------|------|
| `X-RateLimit-Limit` | トークンに対する1分あたりの最大リクエスト数 |
| `X-RateLimit-Remaining` | 現在のウィンドウで残っているリクエスト数 |
| `X-RateLimit-Reset` | レート制限がリセットされる時刻（Unixタイムスタンプ、秒単位） |

### レスポンス例

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1737543600
```

---

## 429エラーの対処

レート制限を超過すると、APIは**HTTP 429 (Too Many Requests)**ステータスコードを返します。

### エラーレスポンス例

```json
{
  "err": "Rate limit exceeded",
  "ECODE": "RATE_LIMIT"
}
```

### 対処方法

1. **X-RateLimit-Resetヘッダーを確認**
   - レート制限がリセットされる時刻まで待機します
   - Unixタイムスタンプ（秒単位）で提供されます

2. **指数バックオフでリトライ**
   - 最初のリトライ: 1秒待機
   - 2回目のリトライ: 2秒待機
   - 3回目のリトライ: 4秒待機
   - 以降、指数的に待機時間を増加

3. **ジッターの追加**
   - ランダムな遅延を追加して、リトライの集中を避ける
   - 例: `wait_time = base_wait * (2 ** retry_count) + random(0, 1000)ms`

---

## ベストプラクティス

### 1. レスポンスヘッダーの監視

すべてのリクエストで`X-RateLimit-Remaining`をチェックし、残りリクエスト数を追跡します。

```python
response = requests.get(url, headers=headers)
remaining = int(response.headers.get('X-RateLimit-Remaining', 0))

if remaining < 10:
    # 残りが少ない場合は処理を調整
    time.sleep(5)
```

### 2. 指数バックオフ＋ジッターでリトライ

429エラーが発生した場合、指数バックオフとジッターを組み合わせてリトライします。

```python
import time
import random

def retry_with_backoff(func, max_retries=5):
    for retry in range(max_retries):
        try:
            return func()
        except RateLimitError:
            if retry == max_retries - 1:
                raise
            wait_time = (2 ** retry) + random.uniform(0, 1)
            time.sleep(wait_time)
```

### 3. キャッシュの活用

- 頻繁にアクセスするデータ（タスク一覧、スペース情報など）はローカルにキャッシュします
- キャッシュの有効期限を設定し、必要に応じて更新します
- 変更が少ないマスターデータは積極的にキャッシュします

### 4. バッチ処理の最適化

- 複数のリクエストが必要な場合、可能な限りバッチAPIを使用します
- リクエストの間に適切な遅延を挿入します
- 並列処理を行う場合は、レート制限を考慮してスレッド数/プロセス数を制限します

### 5. Webhookの活用

- ポーリングの代わりにWebhookを使用して、リアルタイムでイベントを受信します
- これにより、定期的なGETリクエストを削減できます

### 6. リクエストの優先順位付け

- 重要なリクエストを優先的に処理します
- バックグラウンドタスクは、レート制限に余裕がある時に実行します

---

## 実装例

### Pythonでのレート制限対応

```python
import requests
import time
from datetime import datetime

class ClickUpClient:
    def __init__(self, api_token):
        self.api_token = api_token
        self.base_url = "https://api.clickup.com/api/v2"
        self.headers = {"Authorization": api_token}

    def _make_request(self, method, endpoint, **kwargs):
        url = f"{self.base_url}/{endpoint}"

        while True:
            response = requests.request(method, url, headers=self.headers, **kwargs)

            # レート制限情報を取得
            remaining = int(response.headers.get('X-RateLimit-Remaining', 0))
            reset_time = int(response.headers.get('X-RateLimit-Reset', 0))

            if response.status_code == 429:
                # レート制限に達した場合
                wait_time = reset_time - int(time.time())
                print(f"Rate limit exceeded. Waiting {wait_time} seconds...")
                time.sleep(max(wait_time, 1))
                continue

            # 残りが少ない場合は警告
            if remaining < 10:
                print(f"Warning: Only {remaining} requests remaining")

            response.raise_for_status()
            return response.json()

    def get_task(self, task_id):
        return self._make_request("GET", f"task/{task_id}")
```

---

## トラブルシューティング

### 問題: 429エラーが頻繁に発生する

**原因**:
- リクエスト頻度が高すぎる
- 複数のプロセス/スレッドが同時にAPIを呼び出している
- ワークスペースプランの制限が低い

**解決策**:
1. リクエストの間に遅延を追加
2. バッチ処理を使用してリクエスト数を削減
3. キャッシュを活用して重複リクエストを削減
4. 必要に応じてプランのアップグレードを検討

### 問題: X-RateLimit-Remainingが予想より早く減少する

**原因**:
- 同じトークンを複数のアプリケーション/スクリプトで使用している
- Webhookの設定が誤っており、ポーリングが継続している

**解決策**:
1. トークンの使用状況を監査
2. アプリケーションごとに専用のトークンを作成
3. Webhookの設定を確認

---

## 参考リンク

- [Rate Limits - ClickUp Developer Portal](https://developer.clickup.com/docs/rate-limits)
- [Getting Started - ClickUp Developer Portal](https://developer.clickup.com/docs/Getting%20Started)
- [Common Errors - ClickUp Developer Portal](https://developer.clickup.com/docs/common_errors)
- [ClickUp API v2 Documentation](https://clickup.com/api)

---

## 更新履歴

- 2026-01-22: 初版作成
