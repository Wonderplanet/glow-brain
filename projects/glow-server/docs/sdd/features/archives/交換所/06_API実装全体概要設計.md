# API実装全体概要設計

## 1. ドキュメント情報
- 対象機能: 交換所
- 作成日: 2025-11-26
- 参照ドキュメント: 05_サーバーAPI要件書.md

## 2. API実装全体像

### 2.1 関連するAPIエンドポイント一覧

| エンドポイント | 種別 | 関連要件ID | 概要 |
|-------------|------|-----------|------|
| GET /api/exchange/list | 新規作成 | REQ-MASTER-1, REQ-MASTER-2, REQ-MASTER-3, REQ-MASTER-4 | 交換所一覧の取得 |
| GET /api/exchange/:exchange_id/lineups | 新規作成 | REQ-LINEUP-1, REQ-LINEUP-2, REQ-LINEUP-3, REQ-LINEUP-4, REQ-HISTORY-1, REQ-HISTORY-2, REQ-HISTORY-3 | 交換所のラインナップ一覧とユーザー交換履歴の取得 |
| POST /api/exchange/trade | 新規作成 | REQ-EXCHANGE-1～8, REQ-ORIGINAL-1, REQ-MULTI-COST-1 | 交換実行（まとめて交換を含む） |

### 2.2 新規作成APIエンドポイント

#### GET /api/exchange/list
- **関連要件:** REQ-MASTER-1, REQ-MASTER-2, REQ-MASTER-3, REQ-MASTER-4
- **役割:** 現在開催中の交換所一覧を取得する
- **新規作成が必要な理由:**
  - 交換所は既存のShop機能とは独立した機能として設計されている
  - 交換所特有の3種類のカテゴリー（通常、イベント、キャラのかけらBOX）を管理する必要がある
  - 交換所の開催期間と残り時間計算のロジックが必要
  - 既存のShop APIとは別の独立したエンドポイントとして設計することで、将来的な拡張性とメンテナンス性を確保

#### GET /api/exchange/:exchange_id/lineups
- **関連要件:** REQ-LINEUP-1, REQ-LINEUP-2, REQ-LINEUP-3, REQ-LINEUP-4, REQ-HISTORY-1, REQ-HISTORY-2, REQ-HISTORY-3
- **役割:** 特定の交換所のラインナップ一覧とユーザーの交換履歴を取得する
- **新規作成が必要な理由:**
  - 交換所のラインナップは既存のShop商品とは異なる構造を持つ
  - ユーザーの交換回数（trade_count）と通算交換回数（trade_total_count）の二重管理が必要
  - ラインナップごとの交換期間と残り時間計算が必要
  - 月次リセットの判定処理が必要
  - 既存のShop APIとは異なるレスポンス構造が必要

#### POST /api/exchange/trade
- **関連要件:** REQ-EXCHANGE-1～8, REQ-ORIGINAL-1, REQ-MULTI-COST-1
- **役割:** 交換実行（まとめて交換を含む）とリソース消費・報酬付与を行う
- **新規作成が必要な理由:**
  - 交換所特有のバリデーション処理が必要:
    - 交換所とラインナップの開催期間チェック
    - 月次リセット判定
    - まとめて交換の個数計算とバリデーション
    - 複数リソースの同時消費（プランナー確認結果Q1）
    - 原画アイテムの特別処理（原画+かけら16個の同時付与）
  - 既存のShop交換APIとは異なる処理フローが必要
  - 交換ログの記録形式が異なる

### 2.3 既存API改修エンドポイント

該当なし。

- 交換所機能は既存のShop機能とは独立した新機能であり、既存APIの改修は不要
- 既存のShop機能に影響を与えないよう、完全に新規のエンドポイントとして実装

## 3. 要件とAPIの対応関係

### 3.1 要件 REQ-MASTER-1: 交換所の種別管理
- **実現に必要なAPI:**
  - GET /api/exchange/list（新規作成）
- **実装概要:**
  - 交換所マスタから3種類のカテゴリー（通常、イベント、キャラのかけらBOX）を取得
  - カテゴリーごとにリセット方式が異なる（Monthlyリセット、リセットなし）
- **備考:** マスタデータのカテゴリー設定に基づいてフィルタリング

### 3.2 要件 REQ-MASTER-2: 交換所の開催期間管理
- **実現に必要なAPI:**
  - GET /api/exchange/list（新規作成）
- **実装概要:**
  - 現在時刻が開催期間（start_date～end_date）内の交換所のみを返却
  - 開催期間外の交換所は除外
- **備考:** NULLの場合は無期限として扱う

### 3.3 要件 REQ-MASTER-3: 交換所の残り時間計算
- **実現に必要なAPI:**
  - GET /api/exchange/list（新規作成）
- **実装概要:**
  - end_dateがNULLの場合: 「期限なし」を返却
  - end_dateが設定済みの場合: `end_date - 現在時刻`を計算し、「dd日tt時間」形式で返却
- **備考:** クライアント側での表示形式変換を想定

### 3.4 要件 REQ-MASTER-4: 復刻時の交換所ID管理
- **実現に必要なAPI:**
  - GET /api/exchange/list（新規作成）
- **実装概要:**
  - 復刻時は新規の交換所IDを発行（マスタデータ運用で対応）
  - サーバー側でのリセット処理は不要
- **備考:** マスタデータ運用方針のため、API実装への影響は小さい

### 3.5 要件 REQ-LINEUP-1: ラインナップの詳細設定管理
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
- **実装概要:**
  - ラインナップマスタから報酬リソース、必要リソース、交換期間、交換上限数を取得
  - 複数リソース消費に対応したマスタデータ構造
- **備考:** 複数リソース消費の詳細設計が必要

### 3.6 要件 REQ-LINEUP-2: ラインナップの交換期間フィルタリング
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
- **実装概要:**
  - 現在時刻がラインナップの交換期間（start_date～end_date）内のラインナップのみを返却
  - 期間外のラインナップは除外
- **備考:** 交換所の開催期間とは独立したフィルタリング

### 3.7 要件 REQ-LINEUP-3: ラインナップの残り時間計算
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
- **実装概要:**
  - ラインナップのend_dateと現在時刻を比較し、残り時間を「dd日tt時間」形式で返却
  - end_dateがNULLの場合は無期限として扱う
- **備考:** クライアント側での表示形式変換を想定

### 3.8 要件 REQ-LINEUP-4: NULL設定時の無期限・無制限制御
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - end_dateがNULLの場合: 交換期限チェックをスキップ
  - tradable_countがNULLの場合: 交換上限チェックをスキップ
- **備考:** NULL判定は厳密に行う（0とNULLは区別）

### 3.9 要件 REQ-HISTORY-1: 交換回数の二重管理
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - ユーザー交換履歴テーブルに`trade_count`と`trade_total_count`を保持
  - 交換実行時、両カウンタを同時にインクリメント
  - リセット時、`trade_count`のみを0にリセット
- **備考:** レスポンスには`trade_total_count`を含める

### 3.10 要件 REQ-HISTORY-2: リセットタイミングの判定
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - ユーザーアクセス時にClock機能のisFirstMonth()を使用してリセット判定
  - 通常交換所のみMonthlyリセット対象
  - イベント交換所とキャラのかけらBOX交換所はリセットなし
- **備考:** バッチ処理ではなくアクセス時判定で実装

### 3.11 要件 REQ-HISTORY-3: 交換履歴のキャッシュ管理
- **実現に必要なAPI:**
  - GET /api/exchange/:exchange_id/lineups（新規作成）
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - ユーザー交換履歴モデルは`UsrEloquentModel`を継承
  - UsrModelManagerによる自動キャッシュ機構を活用
- **備考:** 既存のキャッシュ機構を踏襲

### 3.12 要件 REQ-EXCHANGE-1: 交換実行の事前バリデーション
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 以下の順序でバリデーションを実施:
    1. 交換所の存在チェックと開催期間チェック
    2. ラインナップの存在チェックと交換期間チェック
    3. 交換回数のリセット判定
    4. 交換上限チェック
    5. リソース所持数チェック
    6. 交換個数の妥当性チェック
- **備考:** いずれかのチェックで失敗した場合、適切なエラーコードをスロー

### 3.13 要件 REQ-EXCHANGE-2: トランザクション内でのリソース消費と報酬付与
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - `UseCaseTrait::applyUserTransactionChanges()`内でトランザクション処理
  - トランザクション内でリソース消費と報酬付与を実行
  - 例外が発生した場合、全ての変更をロールバック
- **備考:** 既存のShop UseCaseパターンを踏襲

### 3.14 要件 REQ-EXCHANGE-3: リソース消費処理
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - コストタイプに応じて以下のサービスを呼び出し:
    - CostType = Coin: `UserDelegator::consumeCoin()`
    - CostType = Diamond: `AppCurrencyDelegator::consumeDiamond()`
    - CostType = PaidDiamond: `AppCurrencyDelegator::consumePaidDiamond()`
    - CostType = Item: `UsrItemService::consumeItem()`
  - 複数リソース消費の場合、順次消費
- **備考:** 既存のShopService::consumeCost()と同様のロジック

### 3.15 要件 REQ-EXCHANGE-4: 報酬付与処理
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - `RewardDelegator::addReward()`で報酬をリストに追加
  - `RewardDelegator::sendRewards()`で一括配布を実行
  - 各SendServiceが自動的に呼び出される
- **備考:** 既存のShopService::tradeShopItem()と同様のRewardDelegator利用パターン

### 3.16 要件 REQ-EXCHANGE-5: 交換回数のインクリメント
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 交換実行時、`trade_count`と`trade_total_count`を両方+1
  - まとめて交換の場合、交換個数分のインクリメント
- **備考:** UsrShopItem::incrementTradeCount()と同様

### 3.17 要件 REQ-EXCHANGE-6: 交換ログの保存
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 交換実行時に以下の情報をログテーブルに保存:
    - usr_user_id, lineup_id, trade_count, cost_type, cost_amount, received_reward, created_at
- **備考:** 既存のLogTradeShopItemと同様のログ保存処理

### 3.18 要件 REQ-EXCHANGE-7: エラーハンドリング
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 以下のエラーコードをスロー:
    - MST_NOT_FOUND: 交換所・ラインナップが見つからない、または期間外
    - SHOP_TRADE_COUNT_LIMIT: 交換上限到達
    - LACK_OF_RESOURCES: リソース不足
    - INVALID_PARAMETER: 交換個数が不正
- **備考:** 既存のShop機能と統一したエラーコード（200番台）

### 3.19 要件 REQ-EXCHANGE-8: まとめて交換の個数検証
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 交換可能個数の計算:
    1. 交換上限の残数: `tradable_count - trade_count`
    2. 所持リソースから計算可能な交換回数: `所持リソース数 / 必要リソース数`
    3. 最大交換個数 = min(1, 2)
  - 交換個数のバリデーション: `交換個数 >= 1` かつ `交換個数 <= 最大交換個数`
- **備考:** 既存のShop機能では単一交換のみのため、新規実装

### 3.20 要件 REQ-ORIGINAL-1: 原画の付与方法
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 原画を交換した際、RewardDelegatorに以下の2つの報酬を登録:
    1. 原画アイテム（Item、数量1）
    2. 原画のかけら（Item、数量16）
  - クライアント側で16ピース演出を実行
- **備考:** 新規実装（既存コードに原画の特別処理の実装例なし）

### 3.21 要件 REQ-MULTI-COST-1: 複数リソース消費の実装
- **実現に必要なAPI:**
  - POST /api/exchange/trade（新規作成）
- **実装概要:**
  - 1つのラインナップに対して、複数種類のコストを設定可能
  - 交換実行時、全てのコストを順次消費
  - いずれかのコストが不足している場合、LACK_OF_RESOURCESエラーをスロー
- **備考:**
  - 複数リソース消費の詳細設計が必要（最大組み合わせ数、バリデーション順序等）
  - テーブル設計の拡張が必要
  - 工数見積もり: +3人日

## 4. 既存APIだけでは実現困難な項目

### 4.1 交換所の独立した管理体系（REQ-MASTER-1～4）
- **困難な理由:**
  - 既存のShop機能は商品の販売に特化しており、交換所の3種類のカテゴリー（通常、イベント、キャラのかけらBOX）を管理する構造を持たない
  - 交換所の開催期間管理と残り時間計算のロジックは、既存のShop商品の期間管理とは異なる仕様
  - 月次リセットの判定処理（Clock::isFirstMonth()）は交換所特有の要件
  - 既存のShop APIに追加すると、Shopドメインの責務が不明確になる
- **解決策:** 新規API `GET /api/exchange/list` を作成

### 4.2 ラインナップの複雑な管理要件（REQ-LINEUP-1～4、REQ-HISTORY-1～3）
- **困難な理由:**
  - 交換所のラインナップは、交換所の開催期間とは独立した交換期間を持つ（二重の期間管理）
  - ユーザーの交換回数（trade_count）と通算交換回数（trade_total_count）の二重管理が必要
  - 月次リセット判定とユーザーごとの交換履歴管理が必要
  - 既存のShop商品とは異なる複雑なレスポンス構造が必要
  - 既存のShop APIに追加すると、複雑度が増し、メンテナンス性が低下
- **解決策:** 新規API `GET /api/exchange/:exchange_id/lineups` を作成

### 4.3 交換実行の特殊な処理フロー（REQ-EXCHANGE-1～8、REQ-ORIGINAL-1、REQ-MULTI-COST-1）
- **困難な理由:**
  - 交換所特有のバリデーション処理が多数存在:
    - 交換所とラインナップの開催期間チェック（二重チェック）
    - 月次リセット判定
    - まとめて交換の個数計算とバリデーション（既存のShop機能にない機能）
    - 複数リソースの同時消費（既存のShop機能では単一リソースのみ）
    - 原画アイテムの特別処理（原画+かけら16個の同時付与）
  - 既存のShop交換APIに追加すると、条件分岐が複雑化し、コードの可読性とメンテナンス性が低下
  - 交換ログの記録形式が異なる（複数リソース消費のログ、まとめて交換の個数情報）
- **解決策:** 新規API `POST /api/exchange/trade` を作成

### 4.4 既存Shopドメインとの責務分離
- **困難な理由:**
  - 既存のShop機能は「ゲーム内通貨やアイテムで商品を購入する」という単純な購買行動をサポート
  - 交換所機能は「複数のリソースを消費して別のリソースと交換する」という複雑な交換行動をサポート
  - 両者は同じ「取引」というカテゴリーに見えるが、ビジネスロジックと管理要件が大きく異なる
  - 既存のShop機能に交換所の要件を統合すると、Shopドメインの責務が不明確になり、将来的な拡張性が損なわれる
- **解決策:** 完全に独立した新規APIエンドポイント群を作成し、Shopドメインとは別のドメイン（Exchangeドメイン）として管理

## 5. 実装優先順位

### 5.1 優先度：高（基盤となるAPI）
1. **GET /api/exchange/list** - 交換所一覧取得（基盤）
   - 交換所の開催期間管理と残り時間計算
   - 3種類のカテゴリーのフィルタリング
   - 工数見積もり: 2人日

2. **GET /api/exchange/:exchange_id/lineups** - ラインナップ一覧と交換履歴取得（基盤）
   - ラインナップの交換期間管理と残り時間計算
   - ユーザー交換履歴の取得と月次リセット判定
   - 交換可能回数の計算
   - 工数見積もり: 3人日

### 5.2 優先度：高（コア機能のAPI）
3. **POST /api/exchange/trade** - 交換実行（単一リソース消費版）
   - 交換実行の事前バリデーション
   - 単一リソースの消費と報酬付与
   - 交換回数のインクリメント
   - 交換ログの保存
   - 工数見積もり: 3人日

### 5.3 優先度：中（拡張機能）
4. **POST /api/exchange/trade** - まとめて交換対応
   - まとめて交換の個数計算とバリデーション
   - 工数見積もり: 1人日

5. **POST /api/exchange/trade** - 原画アイテム対応
   - 原画アイテムの特別処理（原画+かけら16個の同時付与）
   - 工数見積もり: 1人日

### 5.4 優先度：中（複雑な拡張）
6. **POST /api/exchange/trade** - 複数リソース消費対応
   - 複数リソース消費の詳細設計
   - テーブル設計の拡張
   - バリデーションロジックの拡張
   - 工数見積もり: 3人日（詳細設計含む）

### 5.5 実装順序の推奨
1. **Phase 1: 基盤構築**
   - GET /api/exchange/list（2人日）
   - GET /api/exchange/:exchange_id/lineups（3人日）
   - テーブル設計とマイグレーション（2人日）
   - **合計: 7人日**

2. **Phase 2: コア機能実装**
   - POST /api/exchange/trade（単一リソース消費版）（3人日）
   - まとめて交換対応（1人日）
   - 原画アイテム対応（1人日）
   - **合計: 5人日**

3. **Phase 3: 拡張機能実装（詳細設計後）**
   - 複数リソース消費の詳細設計（1人日）
   - 複数リソース消費対応（2人日）
   - **合計: 3人日**

4. **Phase 4: テストと統合**
   - 単体テスト実装（3人日）
   - 統合テスト実装（2人日）
   - **合計: 5人日**

**総工数見積もり: 20人日**

### 5.6 実装時の注意点
- Phase 1とPhase 2は、複数リソース消費を除く全ての基本機能を実装できるため、先行して進めることが可能
- Phase 3の複数リソース消費については、プランナーとの詳細設計完了後に着手
- 各Phase完了後、要件を満たしているかを確認
- 既存のShop機能への影響がないことを確認

## 6. 補足: 既存Shop機能との共通パターン

交換所機能は完全に新規のエンドポイントとして実装するが、以下の既存Shop機能のパターンを踏襲する:

### 6.1 利用する共通基盤
- **トランザクション処理:** `UseCaseTrait::applyUserTransactionChanges()`
- **リソース消費:** `UserDelegator::consumeCoin()`, `AppCurrencyDelegator::consumeDiamond()`, `UsrItemService::consumeItem()`
- **報酬付与:** `RewardDelegator::addReward()`, `RewardDelegator::sendRewards()`
- **キャッシュ管理:** `UsrModelManager`による自動キャッシュ機構
- **リセット判定:** `Clock::isFirstMonth()`

### 6.2 参考にする既存実装
- **ShopTradeShopItemUseCase:** 交換実行の全体フロー
- **ShopService::consumeCost():** リソース消費のパターン
- **ShopService::tradeShopItem():** 報酬付与のパターン
- **UsrShopItem::incrementTradeCount():** 交換回数のインクリメント
- **LogTradeShopItemRepository::create():** 交換ログの保存

## 7. まとめ

### 7.1 実装戦略
- 交換所機能は、既存のShop機能とは独立した新規機能として、完全に新しいエンドポイント群を作成
- 既存のShop機能への影響はゼロ
- 既存の共通基盤（Reward、Transaction、Cache等）を最大限活用
- 段階的な実装（Phase 1→2→3→4）により、リスクを最小化

### 7.2 重要な設計判断
- **独立したドメイン:** Exchangeドメインとして、Shopドメインとは別に管理
- **複数リソース消費:** 詳細設計後にPhase 3で実装（先行して基本機能を実装可能）
- **まとめて交換:** 既存のShop機能にない新機能として実装
- **原画の特別処理:** 新規実装（既存コードに実装例なし）

### 7.3 実装準備状況
- Phase 1とPhase 2（基盤+コア機能）は実装開始可能
- Phase 3（複数リソース消費）はプランナーとの詳細設計完了後に着手
- 総工数見積もり: 20人日
