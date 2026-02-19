# API実装全体概要設計

## 1. ドキュメント情報
- 対象機能: BOXガシャ
- 作成日: 2025-11-26
- 参照ドキュメント: 05_サーバーAPI要件書.md

## 2. API実装全体像

### 2.1 関連するAPIエンドポイント一覧

| エンドポイント | 種別 | 関連要件ID | 概要 |
|-------------|------|-----------|------|
| POST /api/box-gacha/draw | 新規作成 | REQ-DRAW-001, REQ-DRAW-002, REQ-COST-001, REQ-REWARD-001, REQ-LOG-001~003, REQ-MISSION-001 | BOXガシャ実行（抽選・報酬配布） |
| POST /api/box-gacha/next | 新規作成 | REQ-RESET-001, REQ-RESET-002 | BOX進行（次BOXへ移行・手動リセット） |
| GET /api/box-gacha/progress | 新規作成 | REQ-BOX-002, REQ-PROG-001, REQ-PROG-002 | BOX進捗情報取得 |

### 2.2 新規作成APIエンドポイント

#### POST /api/box-gacha/draw
- **関連要件:** REQ-DRAW-001, REQ-DRAW-002, REQ-COST-001, REQ-REWARD-001, REQ-LOG-001~003, REQ-MISSION-001
- **役割:** BOXガシャ抽選を実行し、報酬を配布する
- **新規作成が必要な理由:**
  - BOXガシャ固有の抽選ロジック（残りアイテムから抽選し、除外する）が必要
  - 既存のガチャAPIとは抽選方式が根本的に異なる（確率抽選 vs BOX内抽選）
  - BOX進捗管理（残りアイテム更新、引き回数記録）が必要

#### POST /api/box-gacha/next
- **関連要件:** REQ-RESET-001, REQ-RESET-002
- **役割:** BOXを次に進める（次のBOXへの移行または手動リセット）
- **新規作成が必要な理由:**
  - BOXガシャ固有のBOX進行機能（既存ガチャには存在しない）
  - 次のBOXへの移行、無限ボックスへの移行判定が必要
  - 残りアイテムリストの再初期化が必要

#### GET /api/box-gacha/progress
- **関連要件:** REQ-BOX-002, REQ-PROG-001, REQ-PROG-002
- **役割:** ユーザーの現在のBOX進捗情報を取得する
- **新規作成が必要な理由:**
  - BOXガシャトップ画面で進捗情報（現在BOX番号、残りアイテム数、引いた回数）を表示するため
  - 既存のガチャ情報APIとは返却する情報が異なる

## 3. 要件とAPIの対応関係

### 3.1 要件 REQ-BOX-001: BOXマスタデータの参照
- **実現に必要なAPI:**
  - なし（既存のマスタデータ配信機構を利用）
- **実装概要:**
  - BOXガシャマスタデータは既存のopr_gachasおよびopr_gacha_prizesテーブルに格納
  - BOX固有設定はopr_box_gachasテーブルに格納
  - クライアントには既存のマスタデータ配信機構（S3経由）で配信
- **備考:** 既存のマスタデータ配信機構に従う。新規APIは不要

### 3.2 要件 REQ-BOX-002: ユーザーごとのBOX残りアイテム管理
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）- ガシャ実行時に残りアイテムを更新
  - POST /api/box-gacha/next（新規作成）- BOX進行時に残りアイテムを再初期化
  - GET /api/box-gacha/progress（新規作成）- 残りアイテム数を取得
- **実装概要:**
  - usr_box_gachasテーブルのremaining_prizes_json（JSON形式）で管理
  - draw APIで抽選されたアイテムをJSONから除外
  - next APIで次のBOXの内容に再初期化
- **備考:** BOXガシャの中核機能

### 3.3 要件 REQ-PROG-001: ユーザーごとのBOX番号管理
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）- ガシャ実行後、BOXが空なら次BOXへ
  - POST /api/box-gacha/next（新規作成）- BOX進行時にBOX番号を更新
  - GET /api/box-gacha/progress（新規作成）- 現在BOX番号を取得
- **実装概要:**
  - usr_box_gachasテーブルのcurrent_box_numberで管理
  - BOX番号のインクリメント、無限ボックスへの移行判定
- **備考:** -

### 3.4 要件 REQ-PROG-002: BOX引き回数の記録
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）- 引き回数を記録・更新
  - GET /api/box-gacha/progress（新規作成）- 引き回数を取得
- **実装概要:**
  - usr_box_gachasテーブルのdrew_countで管理
  - 既存のusr_gachasも累積回数として活用
  - ズレチェック（validateDrewCount）を実装
- **備考:** 不正防止のため重要

### 3.5 要件 REQ-DRAW-001: BOXガシャ抽選処理
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - BOX残りアイテムからランダムに抽選
  - 抽選されたアイテムを除外
- **備考:** BOXガシャの中核ロジック

### 3.6 要件 REQ-DRAW-002: 単発引きと複数回引き
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - リクエストパラメータで引く回数を指定
  - マスタで定義された回数（1回、10回など）のみ許可
  - 引く回数が残りアイテム数を超える場合はエラー
- **備考:** -

### 3.7 要件 REQ-RESET-001: 手動BOXリセット
- **実現に必要なAPI:**
  - POST /api/box-gacha/next（新規作成）
- **実装概要:**
  - ユーザーからのBOX進行要求を受け付け
  - BOX番号をインクリメント
  - 残りアイテムリストを次のBOXで再初期化
- **備考:** 途中リセット時の残りアイテムは破棄される仕様

### 3.8 要件 REQ-RESET-002: 無限ボックスのリセット
- **実現に必要なAPI:**
  - POST /api/box-gacha/next（新規作成）
- **実装概要:**
  - 通常BOX最後のBOX番号を超えたら無限ボックスに移行
  - 無限ボックスのBOX進行は同じBOX番号で再初期化
- **備考:** REQ-RESET-001と同じAPIで実現

### 3.9 要件 REQ-COST-001: イベント専用アイテム（アイテムA）の消費
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - ガシャ実行前にアイテムA所持数をチェック
  - 引く回数に応じたアイテムAを消費
  - トランザクション内でコスト消費→報酬配布の順序を厳守
- **備考:** 既存ガチャと同様のコスト消費パターン

### 3.10 要件 REQ-REWARD-001: ガシャ結果の報酬配布
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - RewardDelegatorを使用して報酬配布
  - トランザクション内でコスト消費→報酬配布
- **備考:** 既存ガチャと同様の報酬配布パターン

### 3.11 要件 REQ-REWARD-002: 重複キャラクターの自動変換
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - 報酬配布時に重複キャラを検出し、キャラのかけらアイテムに変換
- **備考:** 既存ガチャと同じ変換処理を流用

### 3.12 要件 REQ-EVENT-001: イベント期間の管理
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
  - POST /api/box-gacha/next（新規作成）
  - GET /api/box-gacha/progress（新規作成）
- **実装概要:**
  - opr_gachasテーブルのstart_at/end_atから開始・終了日時を取得
  - 各API実行前に期限チェック（validateExpiration）
- **備考:** 既存ガチャと同様の期限チェック

### 3.13 要件 REQ-EVENT-002: プレイ回数制限なし
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - プレイ回数制限のバリデーション（validatePlayCount）を実施しない
- **備考:** 既存ガチャとは異なる（プレイ回数制限なし）

### 3.14 要件 REQ-LOG-001~003: ガチャログ記録
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - LogGachaActionにログ記録
  - 外部ログシステムに送信（GachaLogService）
  - ガチャ履歴をキャッシュに保存
- **備考:** 既存ガチャと同様のログ記録パターン

### 3.15 要件 REQ-MISSION-001: ミッショントリガーの送信
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
- **実装概要:**
  - GachaMissionTriggerServiceでトリガー送信
- **備考:** 既存ガチャと同様のトリガー送信パターン

### 3.16 要件 REQ-RESPONSE-001: ガシャ実行結果のレスポンス
- **実現に必要なAPI:**
  - POST /api/box-gacha/draw（新規作成）
  - GET /api/box-gacha/progress（新規作成）
- **実装概要:**
  - 獲得アイテムリスト、ユニット・アイテム差分、ユーザーパラメータ、BOX進捗情報を返却
- **備考:** BOX進捗情報（BOX番号、残りアイテム数）を追加で返す

## 4. 既存APIだけでは実現困難な項目

### 4.1 BOXガシャ抽選ロジック（REQ-DRAW-001）
- **困難な理由:**
  - 既存のガチャは確率抽選（重複あり）を前提としている
  - BOXガシャは「残りアイテムから抽選し、除外する」という全く異なるロジックが必要
  - BOXの残りアイテム管理が必要（既存ガチャにはこの概念がない）
  - 既存のGachaDrawUseCaseに追加すると、ガチャタイプごとの分岐が複雑化し、保守性が低下する
- **解決策:** 新規API `POST /api/box-gacha/draw` を作成

### 4.2 BOX進行機能（REQ-RESET-001, REQ-RESET-002）
- **困難な理由:**
  - 既存のガチャにはBOX進行機能が存在しない
  - 次のBOXへの移行、無限ボックスへの移行判定など、BOXガシャ固有のロジックが必要
  - 残りアイテムリストの再初期化が必要
  - 既存APIに追加すると、BOXガシャ専用のエンドポイントになってしまう
- **解決策:** 新規API `POST /api/box-gacha/next` を作成

### 4.3 BOX進捗情報の取得（REQ-BOX-002, REQ-PROG-001, REQ-PROG-002）
- **困難な理由:**
  - 既存のガチャ情報取得APIとは返却する情報が異なる
  - BOX番号、残りアイテム数、現在BOXの引き回数など、BOXガシャ固有の情報が必要
  - 既存のガチャ情報APIに追加すると、レスポンス構造が複雑化する
- **解決策:** 新規API `GET /api/box-gacha/progress` を作成

### 4.4 既存インフラの活用
- **REQ-BOX-001**: 既存のマスタデータ配信機構を利用
  - opr_gachasおよびopr_gacha_prizesテーブルを活用（新規API不要）
  - BOX固有設定のみopr_box_gachasテーブルに追加
  - 既存のマスタデータ配信機構（S3経由）でクライアントに配信

## 5. 実装優先順位

### 5.1 優先度：高（基盤となるAPI）
1. **GET /api/box-gacha/progress（新規作成）** - BOX進捗情報取得（クライアント側での表示に必要）
2. **POST /api/box-gacha/draw（新規作成）** - BOXガシャ実行（中心となる機能）

### 5.2 優先度：中（追加機能のAPI）
1. **POST /api/box-gacha/next（新規作成）** - BOX進行（ガシャ実行機能の後に実装可能）

### 5.3 実装順序の推奨
1. まず優先度：高のAPIを実装し、基盤を構築
   - DB設計（opr_box_gachas、usr_box_gachasテーブル作成）
   - ドメイン設計（既存テーブルとの連携）
   - BOXガシャ実行のコアロジック実装
2. 次に優先度：中のAPIを実装し、BOX進行機能を追加
3. 各API実装後、要件を満たしているかを確認

### 5.4 実装時の注意点
- **トランザクション管理:** コスト消費→報酬配布の順序を厳守
- **既存実装の流用:**
  - opr_gachas、opr_gacha_prizesテーブルを活用
  - 既存のガチャ実装パターン（GachaDrawUseCase、RewardDelegator等）を参考にする
- **不正防止:** 引いた回数のズレチェック（validateDrewCount）を実装
- **JSON管理:** remaining_prizes_jsonの効率的な管理とパフォーマンス考慮
