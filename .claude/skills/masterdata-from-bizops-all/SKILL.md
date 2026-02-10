---
name: masterdata-from-bizops-all
description: 運営仕様書全体からマスタデータを一括作成する統合スキル。全14個の機能スキルを適切に呼び出し、95テーブル全てのマスタデータを高精度に作成します。依存関係を考慮した実行順序、データ整合性チェック、推測値レポート統合機能を提供します。
---

# マスタデータ一括作成 統合スキル

## 概要

運営仕様書全体をインプットして、マスタデータ設定が必要な全ての機能のマスタデータを高精度に一括作成するワークフローを提供します。

### 統合スキルの位置づけ

このスキルは、Phase 1～3で開発された14個の機能スキル（子スキル）を統合し、運営仕様書全体から全95テーブルのマスタデータを自動生成する**親スキル**です。

### カバー範囲

**全95テーブル、14個の機能スキルを統合**:

| カテゴリ | 機能スキル | テーブル数 | 主要テーブル |
|----------|-----------|------------|--------------|
| **ゲームコンテンツ** | gacha | 6 | OprGacha, OprGachaPrize |
| | hero | 13 | MstUnit, MstAbility, MstAttack |
| | mission | 8 | MstMissionEvent, MstMissionReward |
| | quest-stage | 10 | MstQuest, MstStage |
| | advent-battle | 7 | MstAdventBattle, MstAdventBattleRank |
| | pvp | 2 | MstPvp, MstPvpI18n |
| **ゲーム要素** | item | 2 | MstItem, MstItemI18n |
| | reward | 17 | MstMissionReward等（汎用） |
| | emblem | 2 | MstEmblem, MstEmblemI18n |
| | artwork | 5 | MstArtwork, MstArtworkFragment |
| **運営・イベント** | event-basic | 3 | MstEvent, MstHomeBanner |
| | shop-pack | 7 | MstStoreProduct, MstPack |
| **ゲームシステム** | enemy-autoplayer | 5 | MstEnemyCharacter, MstAutoPlayerSequence |
| | ingame | 7 | MstInGame, MstKomaLine |

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **運営仕様書ファイル** | 全ての運営仕様書ファイルを添付 | 複数ファイル可 |

### 実行方法

運営仕様書ファイル全てを添付して、以下のプロンプトを実行してください:

```
運営仕様書全体からマスタデータを一括作成してください。

添付ファイル:
- ガチャ設計書_地獄楽_いいジャン祭.xlsx
- ヒーロー設計書_地獄楽_新キャラ.xlsx
- ミッション設計書_地獄楽_イベントミッション.xlsx
- クエスト設計書_地獄楽_メインストーリー.xlsx
- アイテム設計書_地獄楽_新アイテム.xlsx
- イベント設計書_地獄楽_いいジャン祭.xlsx
- ショップ設計書_地獄楽_期間限定パック.xlsx
(その他、必要な設計書全て)

パラメータ:
- release_key: 202601010
```

## ワークフロー

### Step 1: 運営仕様書の解析

添付された運営仕様書ファイル全体を解析し、以下を特定します:

**解析内容**:
- 各ファイルの機能カテゴリ（ガチャ、ヒーロー、ミッション等）
- 各機能で作成が必要なマスタデータテーブル
- 設計書内の必須パラメータ抽出

**解析方法**:
- ファイル名から機能カテゴリを推定（例: "ガチャ設計書" → gacha）
- ファイル内容から該当する機能スキルを特定
- 各機能スキルに必要なパラメータを抽出

### Step 2: 必要な機能の特定

解析結果から、必要な機能スキルを特定します:

**特定方法**:
- ガチャ設計書がある → `masterdata-from-bizops-gacha`
- ヒーロー設計書がある → `masterdata-from-bizops-hero`
- ミッション設計書がある → `masterdata-from-bizops-mission`
- クエスト設計書がある → `masterdata-from-bizops-quest-stage`
- アイテム設計書がある → `masterdata-from-bizops-item`
- 報酬設定がある → `masterdata-from-bizops-reward`（汎用）
- イベント設計書がある → `masterdata-from-bizops-event-basic`
- ショップ設計書がある → `masterdata-from-bizops-shop-pack`
- 降臨バトル設計書がある → `masterdata-from-bizops-advent-battle`
- PVP設計書がある → `masterdata-from-bizops-pvp`
- 原画設計書がある → `masterdata-from-bizops-artwork`
- エンブレム設計書がある → `masterdata-from-bizops-emblem`
- 敵キャラ設計書がある → `masterdata-from-bizops-enemy-autoplayer`
- インゲーム設計書がある → `masterdata-from-bizops-ingame`

### Step 3: 依存関係の解析

各機能スキル間の依存関係を解析します:

**依存関係マップ**:
```
アイテム(item) → 報酬(reward) → ミッション(mission)
                              → クエスト・ステージ(quest-stage)
                              → 降臨バトル(advent-battle)

ヒーロー(hero) → ガチャ(gacha)
              → ミッション(mission)
              → クエスト・ステージ(quest-stage)

エンブレム(emblem) → 報酬(reward) → 降臨バトル(advent-battle)

イベント基本設定(event-basic) → 各イベント機能
```

**依存関係のルール**:
- **アイテム** は報酬設定で参照されるため、最初に作成
- **ヒーロー** はガチャやミッション報酬で参照されるため、早期に作成
- **エンブレム** は報酬設定で参照されるため、早期に作成
- **報酬** は各機能スキルで使用される汎用スキルなので、アイテム・ヒーロー・エンブレム後に作成
- **イベント基本設定** は各イベント機能の前提となるため、優先的に作成

### Step 4: 実行順序の決定

依存関係を考慮して、以下の順序で実行します:

**推奨実行順序**:
```
1. masterdata-from-bizops-item（アイテム）
2. masterdata-from-bizops-hero（ヒーロー）
3. masterdata-from-bizops-emblem（エンブレム）
4. masterdata-from-bizops-event-basic（イベント基本設定）
5. masterdata-from-bizops-reward（報酬・汎用）
6. masterdata-from-bizops-gacha（ガチャ）
7. masterdata-from-bizops-quest-stage（クエスト・ステージ）
8. masterdata-from-bizops-mission（ミッション）
9. masterdata-from-bizops-advent-battle（降臨バトル）
10. masterdata-from-bizops-pvp（PVP）
11. masterdata-from-bizops-shop-pack（ショップ・パック）
12. masterdata-from-bizops-artwork（原画）
13. masterdata-from-bizops-enemy-autoplayer（敵・自動行動）
14. masterdata-from-bizops-ingame（インゲーム）
```

**注意**: 実際には、運営仕様書に含まれる機能のみを実行します。

### Step 5: 各機能スキルの順次実行

決定した実行順序に従って、各機能スキルを呼び出します:

**実行方法**:
1. 該当する運営仕様書ファイルを特定
2. 機能スキル用のパラメータを準備
3. 機能スキルを実行（`/masterdata-from-bizops-{機能名}` を呼び出し）
4. 実行結果（生成されたCSV、推測値レポート）を収集
5. エラーがあれば記録し、次の機能スキルへ

**各機能スキルの呼び出し例**:
```
# アイテムスキルの呼び出し
/masterdata-from-bizops-item
パラメータ:
- release_key: 202601010
- アイテム設計書ファイル: item_spec.xlsx

# ヒーローキルの呼び出し
/masterdata-from-bizops-hero
パラメータ:
- release_key: 202601010
- ヒーロー設計書ファイル: hero_spec.xlsx

(以下、同様に各機能スキルを順次実行)
```

### Step 6: データ整合性の全体チェック

各機能スキルで作成されたマスタデータの整合性をチェックします:

**チェック項目**:
1. **外部キー整合性**
   - MstMissionReward.resource_id が MstItem.id または MstUnit.id に存在するか
   - OprGachaPrize.unit_id が MstUnit.id に存在するか
   - その他、テーブル間のリレーション整合性

2. **ID採番の一貫性**
   - リリースキーが全テーブルで統一されているか
   - ID命名規則が守られているか

3. **必須カラムの存在**
   - 必須カラムが全て埋まっているか
   - NULL禁止カラムにNULLがないか

**チェック方法**:
- 各CSVファイルを読み込み
- テーブル間の参照関係を検証
- 不整合があればエラーレポートに記録

### Step 7: 推測値レポートの統合

各機能スキルから出力された推測値レポートを統合します:

**統合方法**:
1. 各機能スキルの推測値レポートを収集
2. 機能別にセクション分け
3. 推測値の重要度（High/Medium/Low）でソート
4. 全体の推測値レポートとして出力

**レポート形式**:
```markdown
# 統合推測値レポート

## 概要
- 総推測値数: 42件
- High（要確認）: 12件
- Medium（推奨確認）: 18件
- Low（参考）: 12件

## 機能別レポート

### ガチャ (masterdata-from-bizops-gacha)
#### High
- OprGacha.display_size: 仮値 "Medium" を設定（運営仕様書に記載なし）
- OprGacha.strapi_uuid: 仮UUID "00000000-0000-0000-0000-000000000001" を設定

#### Medium
- OprGachaI18n.description: ガチャ名から説明文を自動生成

### ヒーロー (masterdata-from-bizops-hero)
...
```

### Step 8: 全体レポートの出力

最終的な全体レポートを出力します:

**レポート内容**:
1. **実行サマリー**
   - 実行した機能スキル一覧
   - 作成したテーブル数
   - 作成したレコード数
   - 実行時間

2. **統合推測値レポート**
   - Step 7で作成したレポート

3. **データ整合性チェック結果**
   - Step 6で検証した結果
   - エラー・警告の一覧

4. **次のステップ**
   - 推測値の確認・修正方法
   - masterdata-csv-validatorスキルでの検証推奨

## 実行順序の詳細

詳細な実行順序とロジックは [workflow-logic.md](workflow-logic.md) を参照してください。

## 出力例

### 全体レポート

```markdown
# マスタデータ一括作成 実行レポート

## 実行サマリー

### 実行日時
2026-01-10 14:30:00 ～ 2026-01-10 14:45:00（15分）

### 実行した機能スキル
1. ✅ masterdata-from-bizops-item（アイテム）
2. ✅ masterdata-from-bizops-hero（ヒーロー）
3. ✅ masterdata-from-bizops-emblem（エンブレム）
4. ✅ masterdata-from-bizops-event-basic（イベント基本設定）
5. ✅ masterdata-from-bizops-reward（報酬・汎用）
6. ✅ masterdata-from-bizops-gacha（ガチャ）
7. ✅ masterdata-from-bizops-quest-stage（クエスト・ステージ）
8. ✅ masterdata-from-bizops-mission（ミッション）
9. ✅ masterdata-from-bizops-advent-battle（降臨バトル）
10. ✅ masterdata-from-bizops-shop-pack（ショップ・パック）

### 作成したテーブル数
- 合計: 65テーブル
- ガチャ: 6テーブル
- ヒーロー: 13テーブル
- ミッション: 8テーブル
- クエスト・ステージ: 10テーブル
- アイテム: 2テーブル
- 報酬: 17テーブル（汎用）
- イベント基本設定: 3テーブル
- 降臨バトル: 7テーブル
- ショップ・パック: 7テーブル

### 作成したレコード数
- 合計: 1,234レコード
- ガチャ: 56レコード
- ヒーロー: 234レコード
- ミッション: 123レコード
- クエスト・ステージ: 345レコード
- アイテム: 45レコード
- 報酬: 189レコード
- イベント基本設定: 12レコード
- 降臨バトル: 78レコード
- ショップ・パック: 34レコード

## 統合推測値レポート

（省略）

## データ整合性チェック結果

### 外部キー整合性
✅ 全ての外部キーが整合しています

### ID採番の一貫性
✅ リリースキーが全テーブルで統一されています（202601010）

### 必須カラムの存在
✅ 全ての必須カラムが埋まっています

## 次のステップ

1. **推測値の確認・修正**
   - 統合推測値レポートを確認し、必要に応じて修正してください
   - High（要確認）の項目は必ず確認してください

2. **masterdata-csv-validatorスキルでの検証**
   - 作成したCSVファイルをmasterdata-csv-validatorスキルで検証してください
   - DB投入前の最終チェックとして実施してください

```

## 注意事項

### エラーハンドリング

**途中で失敗した場合の対応**:
- エラーが発生した機能スキルをスキップし、次の機能スキルに進みます
- エラー内容は全体レポートに記録します
- 依存関係がある場合、後続の機能スキルもスキップする可能性があります

**エラー例**:
```
ERROR: masterdata-from-bizops-gacha で失敗しました
原因: OprGacha.opr_gacha_id が重複しています
対処: ガチャIDを確認し、重複を解消してください

スキップされた機能スキル:
- なし（ガチャは他の機能に依存されていないため）
```

### 進捗状況のレポート

実行中は、以下のような進捗状況を随時レポートします:

```
[1/10] masterdata-from-bizops-item 実行中...
[1/10] masterdata-from-bizops-item 完了（2テーブル、45レコード）

[2/10] masterdata-from-bizops-hero 実行中...
[2/10] masterdata-from-bizops-hero 完了（13テーブル、234レコード）

...
```

### 大量データ処理時の注意

**リリースキー単位で実行**:
- 複数のリリースキーを一度に処理することは推奨しません
- リリースキーごとに分けて実行してください

**ファイルサイズ**:
- 運営仕様書ファイルが大量の場合、処理時間が長くなる可能性があります
- 必要に応じて機能別に分割して実行することも検討してください

## リファレンス

- [workflow-logic.md](workflow-logic.md) - ワークフローロジックの詳細設計
- [examples/full-workflow-example.md](examples/full-workflow-example.md) - 全体ワークフローの実行例

## トラブルシューティング

### Q: 一部の機能スキルだけ実行したい

**A**: 統合スキルは運営仕様書全体を対象としますが、特定の機能のみ実行したい場合は、該当する機能スキルを個別に呼び出してください。

例:
```
# ガチャスキルのみ実行
/masterdata-from-bizops-gacha

# ヒーロースキルのみ実行
/masterdata-from-bizops-hero
```

### Q: エラーが発生して途中で停止した

**A**: 全体レポートのエラーセクションを確認し、該当する機能スキルを個別に実行して原因を特定してください。

### Q: 推測値が多すぎる

**A**: 運営仕様書に記載されていない情報が多い可能性があります。各機能スキルの references/manual.md を確認し、必須情報を運営仕様書に追記してください。

## 検証

作成したマスタデータCSVは、必ず以下の手順で検証してください:

### Step 1: masterdata-csv-validatorスキルで検証

```
/masterdata-csv-validator

対象ファイル: 作成した全CSVファイル
```

### Step 2: 検証結果の確認

検証結果レポートを確認し、エラー・警告がある場合は修正してください。

### Step 3: DB投入前の最終チェック

- リリースキーが正しいか
- ID採番ルールが守られているか
- 外部キー整合性が保たれているか
