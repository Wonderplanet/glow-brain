# API実装全体概要設計

## 1. ドキュメント情報
- 対象機能: 交換所
- 作成日: 2025-11-26
- 参照ドキュメント: 05_サーバーAPI要件書.md

## 2. API実装全体像

### 2.1 関連するAPIエンドポイント一覧

| エンドポイント | 種別 | 関連要件ID | 概要 |
|-------------|------|-----------|------|
| GET /api/exchanges | 新規作成 | REQ-M-1, REQ-M-2, REQ-U-1, REQ-U-2, REQ-U-3, REQ-P-1 | 交換所一覧と全ラインナップ取得 |
| GET /api/exchanges/{id} | 新規作成 | REQ-M-1, REQ-M-2, REQ-M-5, REQ-U-1, REQ-U-2, REQ-U-3 | 特定交換所とラインナップ取得 |
| POST /api/exchanges/lineups/{lineupId}/trade | 新規作成 | REQ-T-1, REQ-T-2, REQ-T-3, REQ-T-4, REQ-U-2, REQ-U-3, REQ-V-1, REQ-V-2, REQ-L-1 | 交換実行 |

### 2.2 新規作成APIエンドポイント

#### GET /api/exchanges
- **関連要件:** REQ-M-1, REQ-M-2, REQ-U-1, REQ-U-2, REQ-U-3, REQ-P-1
- **役割:** 有効な全交換所とラインナップ一覧を取得し、ユーザーの交換履歴と統合して返却
- **新規作成が必要な理由:**
  - 交換所は新機能であり、既存APIでは対応できない
  - 交換所とラインナップの階層構造を持つ独自のレスポンス形式が必要
  - ユーザーごとの交換履歴と残り回数の計算が必要
  - N+1問題を回避した一括取得が必要

#### GET /api/exchanges/{id}
- **関連要件:** REQ-M-1, REQ-M-2, REQ-M-5, REQ-U-1, REQ-U-2, REQ-U-3
- **役割:** 特定の交換所とその配下のラインナップを取得し、ユーザーの交換履歴と統合して返却
- **新規作成が必要な理由:**
  - お知らせやIGNから特定交換所への直接遷移を実現するために必要
  - 交換所IDを指定して詳細情報を取得する既存APIが存在しない

#### POST /api/exchanges/lineups/{lineupId}/trade
- **関連要件:** REQ-T-1, REQ-T-2, REQ-T-3, REQ-T-4, REQ-U-2, REQ-U-3, REQ-V-1, REQ-V-2, REQ-L-1
- **役割:** 指定されたラインナップで交換を実行（複数リソース消費、報酬付与、履歴更新）
- **新規作成が必要な理由:**
  - 交換所の交換処理は新機能であり、既存APIでは対応できない
  - 複数リソースの同時消費という独自のロジックが必要
  - 交換所特有のリセット周期処理（日次/週次/月次）が必要
  - 既存のショップアイテム交換（POST /api/shop/items/{id}/trade）とは別の処理フローが必要

### 2.3 既存API改修エンドポイント

**なし（全て新規作成）**

交換所機能は完全に新規の機能であり、既存APIの改修は不要です。ただし、以下の既存機能との統合があります：

- **キャラのかけらBOX交換所**: 既存の`ItemExchangeSelectItemUseCase`を利用（改修不要、要件 REQ-N-3）
- **報酬配布**: 既存の`RewardDelegator`を利用（改修不要、要件 REQ-T-3）
- **リソース消費**: 既存の`AppCurrencyDelegator`、`UsrItemService`を利用（改修不要、要件 REQ-T-2）

## 3. 要件とAPIの対応関係

### 3.1 要件 REQ-M-1: 交換所マスタの管理と取得
- **実現に必要なAPI:**
  - GET /api/exchanges（新規作成）
  - GET /api/exchanges/{id}（新規作成）
- **実装概要:**
  - GET /api/exchanges で全交換所一覧を取得
  - GET /api/exchanges/{id} で特定交換所の詳細を取得
  - 開催期間、カテゴリー、有効フラグに基づいて表示対象を絞り込む
- **備考:** 両エンドポイントで交換所マスタの取得ロジックを共有

### 3.2 要件 REQ-M-2: 交換所ラインナップマスタの管理と取得
- **実現に必要なAPI:**
  - GET /api/exchanges（新規作成）
  - GET /api/exchanges/{id}（新規作成）
- **実装概要:**
  - 各交換所に属するラインナップを取得し、交換期間で絞り込む
  - ユーザーの交換履歴と突合して残り回数を計算
- **備考:** N+1問題を回避するため、ラインナップを一括取得

### 3.3 要件 REQ-M-3: 複数リソース消費の管理
- **実現に必要なAPI:**
  - GET /api/exchanges（新規作成） - コスト情報の表示
  - GET /api/exchanges/{id}（新規作成） - コスト情報の表示
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成） - コスト消費の実行
- **実装概要:**
  - ラインナップ取得時に、複数のコスト情報を含めて返却
  - 交換実行時に、全てのコストを順次消費
- **備考:** ラインナップコストテーブル（mst_exchange_lineup_costs）から複数コストを取得

### 3.4 要件 REQ-M-4: 多様なアイテムタイプの交換対応
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - RewardDelegatorを使用して、様々なリソースタイプ（アイテム、キャラ、原画等）を配布
- **備考:** 既存のRewardDelegator機能を活用

### 3.5 要件 REQ-M-5: お知らせ・IGNからの特定交換所への直接遷移
- **実現に必要なAPI:**
  - GET /api/exchanges/{id}（新規作成）
- **実装概要:**
  - 交換所IDをパラメータとして受け取り、該当する交換所の詳細とラインナップを返却
- **備考:** お知らせやIGNのリンクに交換所IDを含める

### 3.6 要件 REQ-U-1: ユーザーごとの交換履歴の永続化
- **実現に必要なAPI:**
  - GET /api/exchanges（新規作成） - 履歴取得
  - GET /api/exchanges/{id}（新規作成） - 履歴取得
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成） - 履歴更新
- **実装概要:**
  - 取得系APIで、ユーザー交換履歴を取得して残り回数を計算
  - 交換実行APIで、交換回数をインクリメント
- **備考:** usr_exchange_lineupsテーブルを使用

### 3.7 要件 REQ-U-2: 交換回数のリセット周期による自動リセット処理
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - 交換実行前に、リセット周期（DAILY/WEEKLY/MONTHLY）に基づいてリセット判定
  - last_reset_atと現在時刻を比較し、必要に応じてexchange_countをリセット
- **備考:** 既存のShopService::resetUsrShopItemのパターンを踏襲

### 3.8 要件 REQ-U-3: 交換回数上限の検証処理
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - 交換実行前に、exchange_countとlimit_countを比較
  - 上限超過時はエラーを返す
- **備考:** リセット判定の後に実施

### 3.9 要件 REQ-U-4: 復刻時の交換所ID切り直しと履歴管理
- **実現に必要なAPI:**
  - （APIの変更は不要、マスタデータの運用で対応）
- **実装概要:**
  - 復刻時に新しい交換所ID・ラインナップIDを発番
  - 新しいIDに対する交換履歴は自動的に新規作成される
- **備考:** API実装としては既存の仕組みで対応可能

### 3.10 要件 REQ-U-5: UsrModelManagerによるキャッシュ管理
- **実現に必要なAPI:**
  - 全てのAPI（内部実装でUsrModelManagerを使用）
- **実装概要:**
  - UsrExchangeLineupモデルにmakeModelKeyメソッドを実装
  - UsrModelManagerでメモリ上にキャッシュ
- **備考:** パフォーマンス要件として必須

### 3.11 要件 REQ-T-1: 交換実行のトランザクション管理
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - applyUserTransactionChangesを使用してトランザクション制御
  - コスト消費、報酬付与、カウント更新、ログ記録を1トランザクションで実行
- **備考:** 既存のShopTradeShopItemUseCaseのパターンを踏襲

### 3.12 要件 REQ-T-2: 複数リソースの同時消費処理
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - ラインナップに設定された全てのコストをループで順次消費
  - AppCurrencyDelegator（コイン）、UsrItemService（アイテム）を使用
- **備考:** 既存のShopService::consumeCostの拡張版

### 3.13 要件 REQ-T-3: RewardDelegatorを経由した報酬配布
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - ExchangeLineupRewardクラスを作成し、RewardDelegator::addRewardで追加
  - トランザクション内でRewardDelegator::sendRewardsを実行
- **備考:** 既存のShopItemRewardのパターンを踏襲

### 3.14 要件 REQ-T-4: まとめて交換（複数回交換）の対応
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - リクエストパラメータにamount（交換回数）を受け取る
  - 指定された回数分、コスト消費と報酬配布を実行
- **備考:** 1回のトランザクションで処理

### 3.15 要件 REQ-V-1: リソース所持数の事前チェック
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - 全てのコストについて所持数をチェック
  - AppCurrencyDelegator、UsrItemServiceの消費メソッド内で検証
- **備考:** 不足時はGameExceptionをスロー

### 3.16 要件 REQ-V-2: 交換期間の有効性チェック
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - マスタ取得時にgetActiveXxxByIdメソッドで有効期間チェック
  - 期間外の場合は例外をスロー
- **備考:** 既存のMstShopItemRepository::getActiveShopItemByIdのパターンを踏襲

### 3.17 要件 REQ-L-1: 交換ログの記録と監査証跡
- **実現に必要なAPI:**
  - POST /api/exchanges/lineups/{lineupId}/trade（新規作成）
- **実装概要:**
  - トランザクション内でlog_trade_exchange_lineupsテーブルにレコードを作成
  - ユーザーID、ラインナップID、コスト情報、報酬情報、交換日時を記録
- **備考:** 既存のLogTradeShopItemRepositoryのパターンを踏襲

### 3.18 要件 REQ-P-1: 交換所一覧・ラインナップ一覧のデータ取得最適化
- **実現に必要なAPI:**
  - GET /api/exchanges（新規作成）
- **実装概要:**
  - 有効な交換所マスタを全件取得
  - ラインナップを一括取得（1クエリ）
  - ユーザー交換履歴を一括取得し、突合
  - N+1問題を回避
- **備考:** 既存のShopService::fetchResetActiveUsrShopItemsWithoutSyncModelsのパターンを踏襲

## 4. 既存APIだけでは実現困難な項目

### 4.1 交換所の階層構造とラインナップ管理（REQ-M-1, REQ-M-2）
- **困難な理由:**
  - 交換所とラインナップの階層構造は新しい概念であり、既存APIでは対応できない
  - 既存のショップアイテム機能はラインナップのみをマスタ管理しており、交換所という上位概念が存在しない
  - 交換所ごとにラインナップをグループ化し、バナーで切り替える構造は既存APIで実現不可能
- **解決策:** 新規API `GET /api/exchange-shops` と `GET /api/exchange-shops/{id}` を作成

### 4.2 複数リソースの同時消費処理（REQ-M-3, REQ-T-2）
- **困難な理由:**
  - 既存のショップアイテム機能は単一コスト（cost_type, cost_amount）のみを管理
  - 複数リソースを同時に消費する仕組みが存在しない
  - 既存のconsume系APIに追加すると、ショップアイテムの共通仕様が崩れる
- **解決策:** 新規API `POST /api/exchange-shops/lineups/{lineupId}/trade` で、複数コストをループ処理

### 4.3 日次/週次/月次リセット周期の対応（REQ-U-2）
- **困難な理由:**
  - 既存のショップアイテム機能はDAILY/WEEKLYのみ対応
  - MONTHLYリセットのロジックが未実装
  - イベント交換所で日次/週次リセットを設定可能にする仕様は既存APIで実現不可能
- **解決策:** 新規API `POST /api/exchange-shops/lineups/{lineupId}/trade` で、MONTHLY含む全リセット周期に対応

### 4.4 お知らせ・IGNからの特定交換所への直接遷移（REQ-M-5）
- **困難な理由:**
  - 交換所IDを指定して特定の交換所とラインナップを取得する既存APIが存在しない
- **解決策:** 新規API `GET /api/exchange-shops/{id}` を作成

### 4.5 既存機能との統合で対応可能な項目
- REQ-T-3（報酬配布）: 既存のRewardDelegatorを利用（改修不要）
- REQ-T-2（リソース消費）: 既存のAppCurrencyDelegator、UsrItemServiceを利用（改修不要）
- REQ-N-3（キャラのかけらBOX）: 既存のItemExchangeSelectItemUseCaseを利用（改修不要）

## 5. 実装優先順位

### 5.1 優先度：高（基盤となるAPI）
1. **GET /api/exchanges**（新規作成） - 交換所一覧取得の中心となるAPI
2. **GET /api/exchanges/{id}**（新規作成） - 特定交換所取得とお知らせ連携に必要
3. **POST /api/exchanges/lineups/{lineupId}/trade**（新規作成） - 交換実行の中心となるAPI

### 5.2 実装順序の推奨
1. **フェーズ1: データ取得系API**
   - GET /api/exchanges を実装
   - GET /api/exchanges/{id} を実装
   - 交換所とラインナップの取得、ユーザー交換履歴との突合を実現
   - N+1問題を回避した一括取得を実現

2. **フェーズ2: 交換実行API**
   - POST /api/exchanges/lineups/{lineupId}/trade を実装
   - 複数リソース消費、報酬付与、トランザクション管理を実現
   - リセット処理、上限チェック、ログ記録を実現

3. **フェーズ3: 検証と最適化**
   - 各API実装後、要件を満たしているかを確認
   - パフォーマンステストを実施
   - エラーハンドリングを改善

### 5.3 実装時の注意点
- **既存パターンの踏襲**: 既存のショップアイテム機能のコード構造を参考にし、一貫性のあるコードを書く
- **UsrModelManagerの活用**: キャッシュ管理でパフォーマンスを確保
- **トランザクション管理**: applyUserTransactionChangesを使用し、原子性を保証
- **複数リソース消費**: ループ処理で全コストを消費し、1つでも不足していれば全体を拒否
