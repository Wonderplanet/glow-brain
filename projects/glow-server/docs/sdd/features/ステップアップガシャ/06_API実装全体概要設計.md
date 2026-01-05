# API実装全体概要設計

## 1. ドキュメント情報
- 対象機能: ステップアップガシャ
- 作成日: 2025-12-16
- 参照ドキュメント: 05_サーバーAPI要件書.md

## 2. API実装全体像

### 2.1 関連するAPIエンドポイント一覧

| エンドポイント | 種別 | 関連要件ID | 概要 |
|-------------|------|-----------|------|
| POST /api/gacha/draw/diamond | 既存改修 | REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3 | ダイヤモンドガシャ実行（ステップアップ対応） |
| POST /api/gacha/draw/paid_diamond | 既存改修 | REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3 | 有償ダイヤモンドガシャ実行（ステップアップ対応） |
| POST /api/gacha/draw/item | 既存改修 | REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3 | アイテムガシャ実行（ステップアップ対応） |
| POST /api/gacha/draw/free | 既存改修 | REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3 | 無料ガシャ実行（ステップアップ対応） |
| GET /api/gacha/prize | 既存改修 | REQ-HI-2 | ガシャ賞品情報取得（ステップアップ対応） |
| GET /api/gacha/history | 既存改修 | REQ-HI-1 | ガシャ履歴取得（ステップアップ対応） |
| POST /api/game/update_and_fetch | 既存改修 | REQ-RS-2 | ゲームデータ同期（ステップアップガシャ情報追加） |

### 2.2 新規作成APIエンドポイント

**新規作成APIは0件です。**

実装方針補足.txtに基づき、既存の `/api/gacha/draw/*` APIを拡張する方針のため、新規APIエンドポイントの追加は行いません。

### 2.3 既存API改修エンドポイント

#### POST /api/gacha/draw/diamond
- **関連要件:** REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3
- **現在の機能:** ダイヤモンドを消費してガシャを実行
- **改修内容:**
  - ガシャタイプ判定にステップアップ（`GachaType::STEPUP`）を追加
  - ステップアップガシャの場合の処理フロー追加:
    - ステップ進行状況の取得・検証（`usr_gachas` の `current_step_number`, `loop_count` カラムから取得）
    - 現在ステップの設定取得（`opr_stepup_gacha_steps`）
    - 周回数上限チェック
    - レアリティ条件付き確定枠抽選ロジック
    - ステップ進行処理（`usr_gachas` の `current_step_number`, `loop_count` を更新）
  - レスポンス `usrGacha` にステップアップガシャ情報を追加
  - ステップアップガシャ実行履歴の記録（`log_gacha_actions` に `step_number`, `loop_count` カラムを含めて記録）
- **改修理由:** 既存APIを活用してステップアップガシャ機能を実現するため

#### POST /api/gacha/draw/paid_diamond
- **関連要件:** REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3
- **現在の機能:** 有償ダイヤモンドを消費してガシャを実行
- **改修内容:** POST /api/gacha/draw/diamond と同様
- **改修理由:** 既存APIを活用してステップアップガシャ機能を実現するため

#### POST /api/gacha/draw/item
- **関連要件:** REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3
- **現在の機能:** アイテムを消費してガシャを実行
- **改修内容:** POST /api/gacha/draw/diamond と同様
- **改修理由:** 既存APIを活用してステップアップガシャ機能を実現するため

#### POST /api/gacha/draw/free
- **関連要件:** REQ-GE-1, REQ-GE-2, REQ-GE-3, REQ-GE-4, REQ-VL-1, REQ-VL-2, REQ-VL-3
- **現在の機能:** 無料でガシャを実行
- **改修内容:** POST /api/gacha/draw/diamond と同様
- **改修理由:** 既存APIを活用してステップアップガシャ機能を実現するため

#### GET /api/gacha/prize
- **関連要件:** REQ-HI-2
- **現在の機能:** ガシャの賞品情報（排出確率等）を返却
- **改修内容:**
  - ステップアップガシャの場合、ステップごとの賞品情報を返却
  - 各ステップの確定枠条件（レアリティ、確定数）を含める
  - レスポンス構造の拡張（ステップ情報の追加）
- **改修理由:** クライアントがステップごとの賞品情報を表示するため

#### GET /api/gacha/history
- **関連要件:** REQ-HI-1
- **現在の機能:** ガシャの実行履歴を返却
- **改修内容:**
  - ステップアップガシャの履歴に実行したステップ番号と周回数を含める
  - `log_gacha_actions` テーブルの `step_number`, `loop_count` カラムから情報を取得
  - レスポンス構造の拡張（ステップ情報の追加）
- **改修理由:** ユーザーがステップアップガシャの実行履歴を確認するため

#### POST /api/game/update_and_fetch
- **関連要件:** REQ-RS-2
- **現在の機能:** ゲームデータを同期し、最新状態を返却
- **改修内容:**
  - レスポンスの `usrGacha` 配列にステップアップガシャ情報を追加
  - 各ステップアップガシャの現在ステップ番号、周回数を含める
  - `usr_gachas` テーブルの `current_step_number`, `loop_count` カラムから情報を取得
- **改修理由:** クライアントがステップアップガシャの進行状況を取得するため

## 3. 要件とAPIの対応関係

### 3.1 要件 REQ-MD-1: ステップアップガシャの基本情報管理
- **実現に必要なAPI:** なし（マスタデータ管理のため、API不要）
- **実装概要:** マスタデータテーブル（`opr_stepup_gachas`）で管理
- **備考:** 管理画面側で登録・編集

### 3.2 要件 REQ-MD-2: ステップごとの詳細設定管理
- **実現に必要なAPI:** なし（マスタデータ管理のため、API不要）
- **実装概要:** マスタデータテーブル（`opr_stepup_gacha_steps`）で管理
- **備考:** 管理画面側で登録・編集

### 3.3 要件 REQ-MD-3: ガシャラインナップとレアリティ情報の管理
- **実現に必要なAPI:** なし（マスタデータ管理のため、API不要）
- **実装概要:** 既存のマスタデータテーブル（`opr_gacha_prizes`）で管理
- **備考:** 既存のガシャ賞品管理と同じ

### 3.4 要件 REQ-UD-1: ステップ進行状況の管理
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - ガシャ実行時に `usr_gachas` テーブルの `current_step_number`, `loop_count` カラムを作成・更新
  - 現在ステップ番号と周回数を管理
- **備考:** 全てのガシャ実行APIで共通処理

### 3.5 要件 REQ-UD-2: 通常ガシャ情報の管理
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - 既存の `usr_gachas` テーブル管理ロジックをそのまま使用
  - ステップアップガシャも通常ガシャと同様に記録
- **備考:** 既存実装を活用

### 3.6 要件 REQ-GE-1: ステップアップガシャの実行
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - ガシャタイプ判定でステップアップガシャ用の処理フローに分岐
  - ステップ進行状況取得 → コスト検証 → 抽選 → 報酬配布 → ステップ進行
- **備考:** 既存のガシャ実行フローを拡張

### 3.7 要件 REQ-GE-2: ステップ進行処理
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - ガシャ実行後、`current_step_number` をインクリメント
  - 最終ステップ完了時に周回処理（ステップ1に戻る、`loop_count++`）
- **備考:** REQ-GE-1の一部として実装

### 3.8 要件 REQ-GE-3: 確定枠抽選処理
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - 現在ステップの `fixed_prize_count`, `fixed_prize_rarity_threshold_type` を取得
  - 最後のN回を確定枠抽選、残りを通常抽選
  - レアリティ条件を満たすアイテムから重み付き抽選
- **備考:** 既存の確定枠ロジックを拡張

### 3.9 要件 REQ-GE-4: 無料ステップの処理
- **実現に必要なAPI:**
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - `cost_type = 'Free'` の場合、コスト消費をスキップ
  - `is_first_free` フラグと `loop_count` でコスト設定を切り替え
- **備考:** 既存の無料ガシャ処理を活用

### 3.10 要件 REQ-RS-1: ガシャ実行レスポンスの拡張
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - レスポンスの `usrGacha` にステップアップガシャ情報を直接追加
  - `currentStepNumber`, `loopCount` フィールドを追加（ステップアップガシャの場合のみNULL以外）
- **備考:** スキーマ定義の拡張が必要

### 3.11 要件 REQ-RS-2: update_and_fetchレスポンスの拡張
- **実現に必要なAPI:**
  - POST /api/game/update_and_fetch（既存改修）
- **実装概要:**
  - `usrGacha` 配列の各要素にステップアップガシャ情報を追加
  - REQ-RS-1と同じ構造
- **備考:** スキーマ定義の拡張が必要

### 3.12 要件 REQ-HI-1: ガシャ履歴の管理
- **実現に必要なAPI:**
  - GET /api/gacha/history（既存改修）
  - POST /api/gacha/draw/*（既存改修、履歴記録処理）
- **実装概要:**
  - ガシャ実行時に `log_gacha_actions` テーブルの `step_number`, `loop_count` カラムに記録
  - 履歴取得時にステップ番号と周回数を含めて返却
- **備考:** 既存の履歴管理ロジックを拡張

### 3.13 要件 REQ-HI-2: ガシャ賞品情報の取得
- **実現に必要なAPI:**
  - GET /api/gacha/prize（既存改修）
- **実装概要:**
  - ステップアップガシャの場合、ステップごとの賞品情報を返却
  - 各ステップの確定枠条件も含める
- **備考:** レスポンス構造の拡張が必要

### 3.14 要件 REQ-VL-1: ステップ順序の検証
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - サーバー側で `usr_gachas.current_step_number` を管理
  - ステップスキップを防止
- **備考:** REQ-GE-1の一部として実装

### 3.15 要件 REQ-VL-2: 周回数上限の検証
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - ガシャ実行前に `loop_count` が `max_loop_count` 未満であることを確認
  - 上限到達時はエラー返却
- **備考:** REQ-GE-1の一部として実装

### 3.16 要件 REQ-VL-3: コストの整合性検証
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - 現在ステップのコスト設定とリクエストパラメータを比較検証
  - 不一致の場合はエラー返却（チート対策）
- **備考:** 既存のコスト検証ロジックを活用

### 3.17 要件 REQ-VL-4: ガシャ期間の検証
- **実現に必要なAPI:**
  - POST /api/gacha/draw/diamond（既存改修）
  - POST /api/gacha/draw/paid_diamond（既存改修）
  - POST /api/gacha/draw/item（既存改修）
  - POST /api/gacha/draw/free（既存改修）
- **実装概要:**
  - 既存の期限検証処理をそのまま使用
- **備考:** 既存実装を活用

### 3.18 要件 REQ-CL-1: ガシャ期間終了時のデータ削除
- **実現に必要なAPI:** なし（バッチ処理または遅延削除）
- **実装概要:**
  - ガシャ期間終了後、`usr_gachas` テーブルの `current_step_number`, `loop_count` をNULLにクリア
  - またはusr_gachasレコード自体を削除（既存の運用方針に従う）
  - アクセス時に期限チェックして遅延削除を推奨
- **備考:** 既存のガシャデータクリーンアップパターンを参考

## 4. 既存APIだけでは実現困難な項目

### 4.1 実現困難な項目は0件

実装方針補足.txtに基づき、既存の `/api/gacha/draw/*` APIを拡張することで全ての要件を実現できます。新規APIエンドポイントの追加は不要です。

### 4.2 既存API改修で対応可能な理由

#### ガシャ実行API（/api/gacha/draw/*）
- **対応可能な理由:**
  - 既存のガシャタイプ判定処理にステップアップガシャ用の分岐を追加することで対応可能
  - トランザクション管理、コスト消費、報酬配布などの既存の共通処理を活用できる
  - ステップ進行処理は、ガシャ実行後のDB更新処理として追加できる
  - レスポンス構造の拡張（`usrGacha` への情報追加）で対応可能

#### ガシャ賞品情報API（/api/gacha/prize）
- **対応可能な理由:**
  - ステップアップガシャの場合の条件分岐を追加することで対応可能
  - レスポンス構造の拡張で、ステップごとの情報を返却できる

#### ガシャ履歴API（/api/gacha/history）
- **対応可能な理由:**
  - 既存の履歴取得ロジックを拡張し、ステップアップガシャ用のログテーブルも参照することで対応可能
  - レスポンス構造の拡張で、ステップ情報を含めて返却できる

#### ゲームデータ同期API（/api/game/update_and_fetch）
- **対応可能な理由:**
  - 既存のガシャ情報取得処理を拡張し、ステップアップガシャ情報も含めて返却することで対応可能

## 5. 実装優先順位

### 5.1 優先度：高（基盤となるAPI）

#### フェーズ1: マスタデータとスキーマ定義
1. スキーマ定義の拡張（`glow-schema/Schema/Gacha.yml`）
   - `GachaType` に `StepUp` を追加
   - `UsrGachaData` に `currentStepNumber`, `loopCount` フィールドを追加
   - ステップアップガシャ用のデータ型を定義
2. マスタデータテーブルの作成
   - `opr_stepup_gachas` テーブル
   - `opr_stepup_gacha_steps` テーブル
3. 既存テーブルへのカラム追加
   - `usr_gachas` テーブルに `current_step_number`, `loop_count` カラムを追加
   - `log_gacha_actions` テーブルに `step_number`, `loop_count` カラムを追加

#### フェーズ2: ガシャ実行APIの拡張
1. POST /api/gacha/draw/diamond（ステップアップ対応）
2. POST /api/gacha/draw/paid_diamond（ステップアップ対応）
3. POST /api/gacha/draw/item（ステップアップ対応）
4. POST /api/gacha/draw/free（ステップアップ対応）

### 5.2 優先度：中（情報取得API）

#### フェーズ3: 情報取得APIの拡張
1. GET /api/gacha/prize（ステップアップ対応）
2. GET /api/gacha/history（ステップアップ対応）
3. POST /api/game/update_and_fetch（ステップアップ対応）

### 5.3 優先度：低（データクリーンアップ）

#### フェーズ4: 運用機能
1. ガシャ期間終了時のデータ削除処理

### 5.4 実装順序の推奨

1. **フェーズ1を完了させる**
   - スキーマ定義とテーブル設計を確定
   - マイグレーションファイルを作成・実行
   - Enum定義（`GachaType::STEPUP`）を追加

2. **フェーズ2を実装する**
   - まず1つのガシャ実行API（例：diamond）でステップアップガシャ機能を実装
   - 動作確認後、他のガシャ実行APIに展開
   - 各実装後、要件を満たしているかをテストで確認

3. **フェーズ3を実装する**
   - ガシャ実行APIが完成してから、情報取得APIを実装
   - レスポンス構造の拡張を実施

4. **フェーズ4を実装する**
   - 基本機能が全て完成してから、運用機能を実装

### 5.5 各フェーズの完了条件

#### フェーズ1完了条件
- マイグレーションが正常に実行できる
- スキーマ定義から自動生成されるコードがビルドできる
- `GachaType::STEPUP` が使用できる

#### フェーズ2完了条件
- ステップアップガシャを実行できる
- ステップが正しく進行する（1→2→...→最大ステップ→1）
- 周回数が正しくカウントされる
- 確定枠抽選が正しく動作する
- レスポンスにステップアップガシャ情報が含まれる

#### フェーズ3完了条件
- 賞品情報APIでステップごとの情報が取得できる
- 履歴APIでステップ情報が取得できる
- update_and_fetchでステップアップガシャ情報が取得できる

#### フェーズ4完了条件
- ガシャ期間終了後、データが適切に削除される

## 6. 実装上の注意事項

### 6.1 既存ガシャ機能への影響

- **影響範囲:** ガシャ実行API全般（`/api/gacha/draw/*`）
- **注意点:**
  - ステップアップガシャ以外のガシャタイプに影響を与えないこと
  - 条件分岐で明確にステップアップガシャのみの処理を分離
  - 既存のテストケースが全て通ることを確認

### 6.2 レスポンス構造の後方互換性

- **影響範囲:** 全てのガシャ関連API
- **注意点:**
  - `usrGacha` への情報追加は、既存フィールドに影響を与えない形で実施
  - ステップアップガシャ以外の場合、`currentStepNumber`, `loopCount` は `null`
  - クライアント側でステップアップガシャ情報が存在するかをチェック

### 6.3 トランザクション管理

- **影響範囲:** ガシャ実行API全般
- **注意点:**
  - ステップ進行処理も含めて、全てを同一トランザクション内で実行
  - ステップ進行に失敗した場合、ガシャ実行全体をロールバック
  - 既存のトランザクション管理パターンを踏襲

### 6.4 パフォーマンス

- **影響範囲:** ガシャ実行API全般
- **注意点:**
  - ステップアップガシャ用のDB検索が追加されるため、適切なインデックスを設定
  - N+1問題が発生しないよう、必要なデータを一括取得
  - 既存ガシャのパフォーマンスに影響を与えないこと

### 6.5 セキュリティ

- **影響範囲:** ガシャ実行API全般
- **注意点:**
  - コスト整合性検証を厳密に実施（チート対策）
  - ステップ順序の検証を実施（不正なステップ実行の防止）
  - サーバー側の状態（`usr_gachas` の `current_step_number`, `loop_count`）を信頼し、クライアントのリクエストは参考程度に扱う
