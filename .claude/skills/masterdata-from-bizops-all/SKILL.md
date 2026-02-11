---
name: masterdata-from-bizops-all
description: 運営仕様書全体からマスタデータを一括作成する統合スキル。全14個の機能スキルを適切に呼び出し、95テーブル全てのマスタデータを高精度に作成します。依存関係を考慮した実行順序、データ整合性チェック、推測値レポート統合機能を提供します。
---

# マスタデータ一括作成 統合スキル

## 概要

運営仕様書全体をインプットして、マスタデータ設定が必要な全ての機能のマスタデータを高精度に一括作成するワークフローを提供します。

### 統合スキルの位置づけ

このスキルは、Phase 1～3で開発された14個の機能スキル（子スキル）を統合し、運営仕様書全体から全95テーブルのマスタデータを自動生成する**親スキル**です。

### 🎯 重要な実行原則

**スキル実行率100%を目指してください**:
- 全14個の機能スキルについて、実行の要否を必ず判定する
- 運営仕様書ファイルを慎重に分析し、該当する可能性のあるスキルは全て実行を試みる
- ファイル名だけでなく、シート名・内容キーワードも総合的に判断する
- スキップする場合は明確な理由を記録する
- 最終レポートに**スキル実行率（実行数/14）**を必ず記載する

**目標**: スキル実行率 100%（14/14スキル実行）、ファイル生成率 85%以上

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

#### 方法1: 運営仕様書ファイルを直接添付

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

#### 方法2: specs.csvファイルから運営仕様書パスを読み込む（推奨）

運営仕様書がCSV形式でディレクトリに保存されている場合、specs.csvファイルのパスを指定して実行できます:

```
運営仕様書のパスリスト(specs.csv)からマスタデータを一括作成してください。

パラメータ:
- release_key: 202601010
- specs_csv_path: domain/raw-data/masterdata/released/202601010/specs/specs.csv
```

**specs.csvの形式**:
```csv
path
"domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書"
"domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスタ/地獄楽/ヒーロー基礎設計_chara_jig_00401_賊王 亜左 弔兵衛"
...
```

このスキルは、specs.csvに記載された各パスを読み込み、Step 1の解析ロジックを適用して適切な機能スキルを呼び出します。

## ワークフロー

### Step 1: 運営仕様書の解析

添付された運営仕様書ファイル全体を解析し、以下を特定します:

**解析内容**:
- 各ファイルの機能カテゴリ（ガチャ、ヒーロー、ミッション等）
- 各機能で作成が必要なマスタデータテーブル
- 設計書内の必須パラメータ抽出

**解析方法（複合的に判定）**:
1. **ファイル名パターン**から機能カテゴリを推定
2. **シート名・タブ名**から機能カテゴリを推定
3. **ファイル内容のキーワード**から機能カテゴリを特定
4. **specs.csvのメタデータ**（存在する場合）から判定

**重要**: 各ファイルを慎重に分析し、複数の判定基準を組み合わせて、確実に該当する機能スキルを特定してください。

### Step 2: 必要な機能の特定

解析結果から、必要な機能スキルを特定します。以下のスキル呼び出しトリガールールを使用してください:

#### 🎯 スキル呼び出しトリガールール

各スキルに対して、以下の判定基準のいずれかに該当する場合、そのスキルを呼び出してください:

##### 1. hero（ヒーロー・キャラクター）
**ファイル名パターン**:
- `ヒーロー基礎設計_*`, `ヒーロー設計書_*`, `キャラクター設計_*`, `ユニット設計_*`

**シート名パターン**:
- `キャラクター`, `ユニット`, `Hero`, `Unit`, `アビリティ`, `Ability`, `攻撃`, `Attack`

**内容キーワード**:
- `unit_id`, `attack_id`, `ability_id`, `キャラクター名`, `ユニット名`, `アタック名`, `アビリティ名`

##### 2. gacha（ガチャ）
**ファイル名パターン**:
- `ガチャ設計書_*`, `ガシャ設計書_*`, `Gacha_*`

**シート名パターン**:
- `ガチャ`, `ガシャ`, `Gacha`, `景品`, `Prize`

**内容キーワード**:
- `opr_gacha_id`, `prize_id`, `gacha_upper`, `天井`, `ガチャ名`

##### 3. mission（ミッション）
**ファイル名パターン**:
- `ミッション設計書_*`, `ミッション_*`, `Mission_*`

**シート名パターン**:
- `ミッション`, `Mission`, `達成条件`, `報酬`

**内容キーワード**:
- `mission_id`, `ミッション名`, `達成条件`, `ミッション報酬`, `mission_event_id`

##### 4. quest-stage（クエスト・ステージ）
**ファイル名パターン**:
- `クエスト設計書_*`, `クエスト_*`, `Quest_*`, `ステージ設計_*`

**シート名パターン**:
- `クエスト`, `Quest`, `ステージ`, `Stage`, `話数`

**内容キーワード**:
- `quest_id`, `stage_id`, `クエスト名`, `ステージ名`, `話数`, `ボス`

##### 5. advent-battle（降臨バトル）
**ファイル名パターン**:
- `降臨バトル設計書_*`, `降臨_*`, `Advent_*`

**シート名パターン**:
- `降臨バトル`, `降臨`, `Advent`, `ランキング`

**内容キーワード**:
- `advent_battle_id`, `降臨バトル名`, `ランク`, `難易度`

##### 6. pvp（ランクマッチ・PVP）
**ファイル名パターン**:
- `ランクマッチ設計書_*`, `PvP設計書_*`, `PVP_*`

**シート名パターン**:
- `ランクマッチ`, `PVP`, `PvP`, `対戦`

**内容キーワード**:
- `mst_pvp_id`, `ランクマッチ`, `対戦`

##### 7. item（アイテム）
**ファイル名パターン**:
- `アイテム設計書_*`, `Item_*`

**シート名パターン**:
- `アイテム`, `Item`, `消費アイテム`

**内容キーワード**:
- `mst_item_id`, `アイテム名`, `アイテムタイプ`

##### 8. reward（報酬・汎用）
**ファイル名パターン**:
- `報酬設計_*`, `Reward_*`

**シート名パターン**:
- `報酬`, `Reward`, `初回報酬`, `クリア報酬`

**内容キーワード**:
- `報酬`, `reward`, `resource_type`, `resource_id`

**注意**: rewardスキルは汎用的なため、mission, quest-stage, advent-battle等の他スキル実行後に呼び出すことを推奨

##### 9. emblem（エンブレム）
**ファイル名パターン**:
- `エンブレム設計書_*`, `Emblem_*`

**シート名パターン**:
- `エンブレム`, `Emblem`, `称号`

**内容キーワード**:
- `mst_emblem_id`, `エンブレム名`, `称号`

##### 10. artwork（原画）
**ファイル名パターン**:
- `原画設計書_*`, `アートワーク_*`, `Artwork_*`

**シート名パターン**:
- `原画`, `Artwork`, `アートワーク`, `フラグメント`

**内容キーワード**:
- `artwork_id`, `fragment_id`, `原画名`, `フラグメント`

##### 11. event-basic（イベント基本設定）
**ファイル名パターン**:
- `イベント設計書_*`, `Event_*`, `ホームバナー_*`

**シート名パターン**:
- `イベント`, `Event`, `ホームバナー`, `Banner`

**内容キーワード**:
- `mst_event_id`, `イベント名`, `event_name`, `banner`

##### 12. shop-pack（ショップ・パック）
**ファイル名パターン**:
- `ショップ設計書_*`, `Shop_*`, `パック設計_*`, `Pack_*`

**シート名パターン**:
- `ショップ`, `Shop`, `パック`, `Pack`, `商品`

**内容キーワード**:
- `store_product_id`, `pack_id`, `商品名`, `パック名`

##### 13. enemy-autoplayer（敵・自動行動）
**ファイル名パターン**:
- `敵設計書_*`, `Enemy_*`, `敵キャラ_*`, `AutoPlayer_*`

**シート名パターン**:
- `敵`, `Enemy`, `敵キャラ`, `自動行動`, `AutoPlayer`, `シーケンス`

**内容キーワード**:
- `enemy_character_id`, `enemy_outpost_id`, `auto_player_sequence_id`, `敵キャラ名`

##### 14. ingame（インゲーム設定）
**ファイル名パターン**:
- `インゲーム設計書_*`, `InGame_*`, `演出設計_*`, `コマ割り_*`

**シート名パターン**:
- `インゲーム`, `InGame`, `ゲーム内`, `コマ割り`, `Page`, `演出`

**内容キーワード**:
- `mst_in_game_id`, `background_asset_key`, `koma_line_id`, `page_id`, `演出`

#### 🔄 フォールバック機構

**重要**: 上記のトリガールールでマッチしなかったファイルも、必ず全14個のスキルに対して確認してください。

- 各ファイルを慎重に分析し、少しでも該当する可能性があるスキルは実行を試みる
- 複数のスキルにマッチする場合は、全て実行する
- どのスキルにもマッチしなかったファイルは、最終レポートで警告として記録する

**実行確認リスト（必須）**:
全14個のスキルについて、実行の要否を明示的に判定し、記録してください:
1. ✓ hero - 実行 / ✗ スキップ（理由: xxx）
2. ✓ gacha - 実行 / ✗ スキップ（理由: xxx）
3. ✓ mission - 実行 / ✗ スキップ（理由: xxx）
4. ✓ quest-stage - 実行 / ✗ スキップ（理由: xxx）
5. ✓ advent-battle - 実行 / ✗ スキップ（理由: xxx）
6. ✓ pvp - 実行 / ✗ スキップ（理由: xxx）
7. ✓ item - 実行 / ✗ スキップ（理由: xxx）
8. ✓ reward - 実行 / ✗ スキップ（理由: xxx）
9. ✓ emblem - 実行 / ✗ スキップ（理由: xxx）
10. ✓ artwork - 実行 / ✗ スキップ（理由: xxx）
11. ✓ event-basic - 実行 / ✗ スキップ（理由: xxx）
12. ✓ shop-pack - 実行 / ✗ スキップ（理由: xxx）
13. ✓ enemy-autoplayer - 実行 / ✗ スキップ（理由: xxx）
14. ✓ ingame - 実行 / ✗ スキップ（理由: xxx）

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

**重要**: Step 2で特定した全てのスキルを、依存関係を考慮した順序で実行してください。

**実行フロー**:
1. 該当する運営仕様書ファイルを特定
2. 機能スキル用のパラメータを準備（release_key、ファイルパス等）
3. **Skillツールを使って機能スキルを実行**（例: `skill: "masterdata-from-bizops-hero"`）
4. 実行結果（生成されたCSV、推測値レポート）を収集
5. エラーがあれば詳細を記録し、次の機能スキルへ継続
6. **全てのスキルについて実行完了/スキップの記録を残す**

**各機能スキルの呼び出し例**:
```
# 1. アイテムスキルの実行
/masterdata-from-bizops-item
パラメータ:
- release_key: 202601010
- 運営仕様書ファイル: アイテム設計書_地獄楽.xlsx

# 2. ヒーロースキルの実行
/masterdata-from-bizops-hero
パラメータ:
- release_key: 202601010
- 運営仕様書ファイル: ヒーロー基礎設計_地獄楽.xlsx

# 3. エンブレムスキルの実行
/masterdata-from-bizops-emblem
パラメータ:
- release_key: 202601010
- 運営仕様書ファイル: エンブレム設計書_地獄楽.xlsx

# ... 以下、同様に全14個のスキルを順次実行
```

**進捗レポートの作成**:
各スキル実行後、以下のような進捗レポートを出力してください:
```
[1/14] item: ✓ 実行完了（2テーブル、45レコード生成）
[2/14] hero: ✓ 実行完了（13テーブル、234レコード生成）
[3/14] emblem: ✓ 実行完了（2テーブル、12レコード生成）
[4/14] event-basic: ✗ スキップ（理由: 該当する運営仕様書なし）
[5/14] reward: ✓ 実行完了（17テーブル、189レコード生成）
[6/14] gacha: ✓ 実行完了（6テーブル、56レコード生成）
[7/14] quest-stage: ✓ 実行完了（10テーブル、345レコード生成）
[8/14] mission: ✓ 実行完了（8テーブル、123レコード生成）
[9/14] advent-battle: ✓ 実行完了（7テーブル、78レコード生成）
[10/14] pvp: ✗ スキップ（理由: 該当する運営仕様書なし）
[11/14] shop-pack: ✓ 実行完了（7テーブル、34レコード生成）
[12/14] artwork: ✗ スキップ（理由: 該当する運営仕様書なし）
[13/14] enemy-autoplayer: ✓ 実行完了（5テーブル、67レコード生成）
[14/14] ingame: ✓ 実行完了（7テーブル、89レコード生成）

実行サマリー:
- 実行: 11/14スキル (78.6%)
- スキップ: 3/14スキル (21.4%)
- 総テーブル数: 84個
- 総レコード数: 1,272件
```

**エラーハンドリング**:
- エラーが発生しても処理を中断せず、可能な限り全スキルを実行してください
- エラー内容は詳細に記録し、最終レポートに含めてください
- 依存関係のあるスキルがエラーした場合、依存先スキルもスキップし、その旨を記録してください

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

4. **テーブル依存関係の整合性** (新機能)
   - 親テーブルが存在する場合、対応する子テーブルも存在するか
   - config/table_dependencies.jsonを参照して自動チェック
   - 欠落している子テーブルをレポート

**チェック方法**:
```typescript
/**
 * テーブル間の整合性をチェック
 */
function validateTableIntegrity(allTables: Map<string, any[]>): ValidationResult[] {
  const errors: ValidationResult[] = []

  // 依存関係定義を読み込み
  const TABLE_DEPENDENCIES = {
    "MstPack": ["MstPackContent", "MstPackI18n"],
    "MstStoreProduct": ["MstStoreProductI18n"],
    "MstUnit": ["MstUnitI18n", "MstUnitAbility"],
    "MstItem": ["MstItemI18n"],
    "MstEmblem": ["MstEmblemI18n"],
    "MstEvent": ["MstEventI18n"],
    "OprGacha": ["OprGachaI18n"],
    "MstQuest": ["MstQuestI18n"],
    "MstStage": ["MstStageI18n"],
    "MstAdventBattle": ["MstAdventBattleI18n"]
  }

  for (const [parentTable, childTables] of Object.entries(TABLE_DEPENDENCIES)) {
    if (!allTables.has(parentTable)) continue

    for (const childTable of childTables) {
      if (!allTables.has(childTable)) {
        errors.push({
          type: "missing_child_table",
          parent: parentTable,
          child: childTable,
          message: `親テーブル ${parentTable} があるが、子テーブル ${childTable} が存在しない`
        })
      }
    }
  }

  return errors
}
```

**チェック実行タイミング**:
- 各CSVファイルを読み込み
- テーブル間の参照関係を検証
- 依存関係の整合性を検証（新機能）
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
   - **スキル実行率**: 実行した機能スキル数 / 14 （例: 11/14 = 78.6%）
   - **ファイル生成率**: 生成したファイル数 / 総ファイル数（past_tablesとの比較）
   - 実行した機能スキル一覧（✓実行完了 / ✗スキップ / ⚠エラー）
   - 作成したテーブル数（機能別）
   - 作成したレコード数（機能別）
   - 実行時間

2. **スキル実行詳細**
   - 全14個のスキルの実行状況を明示
   - スキップした理由を明記
   - エラーが発生した場合は詳細を記載

3. **統合推測値レポート**
   - Step 7で作成したレポート

4. **データ整合性チェック結果**
   - Step 6で検証した結果
   - エラー・警告の一覧

5. **次のステップ**
   - 推測値の確認・修正方法
   - masterdata-csv-validatorスキルでの検証推奨
   - 未生成ファイルがある場合の対応方法

## 実行順序の詳細

詳細な実行順序とロジックは [workflow-logic.md](workflow-logic.md) を参照してください。

## 出力例

### 全体レポート

```markdown
# マスタデータ一括作成 実行レポート

## 実行サマリー

### 実行日時
2026-01-10 14:30:00 ～ 2026-01-10 14:45:00（15分）

### スキル実行率
- **実行: 11/14スキル (78.6%)**
- スキップ: 3/14スキル (21.4%)

### 実行した機能スキル詳細
1. ✅ masterdata-from-bizops-item（アイテム）- 2テーブル、45レコード
2. ✅ masterdata-from-bizops-hero（ヒーロー）- 13テーブル、234レコード
3. ✅ masterdata-from-bizops-emblem（エンブレム）- 2テーブル、12レコード
4. ✅ masterdata-from-bizops-event-basic（イベント基本設定）- 3テーブル、8レコード
5. ✅ masterdata-from-bizops-reward（報酬・汎用）- 17テーブル、189レコード
6. ✅ masterdata-from-bizops-gacha（ガチャ）- 6テーブル、56レコード
7. ✅ masterdata-from-bizops-quest-stage（クエスト・ステージ）- 10テーブル、345レコード
8. ✅ masterdata-from-bizops-mission（ミッション）- 8テーブル、123レコード
9. ✅ masterdata-from-bizops-advent-battle（降臨バトル）- 7テーブル、78レコード
10. ✗ masterdata-from-bizops-pvp（PVP）- スキップ（理由: 該当する運営仕様書なし）
11. ✅ masterdata-from-bizops-shop-pack（ショップ・パック）- 7テーブル、34レコード
12. ✗ masterdata-from-bizops-artwork（原画）- スキップ（理由: 該当する運営仕様書なし）
13. ✗ masterdata-from-bizops-enemy-autoplayer（敵・自動行動）- スキップ（理由: 該当する運営仕様書なし）
14. ✅ masterdata-from-bizops-ingame（インゲーム）- 7テーブル、110レコード

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

## 差分生成モード（過去データ自動継承）

### 概要

運営仕様書に記載がないテーブルは、過去データを自動継承します。これにより、変更があったテーブルのみを運営仕様書に記載すれば良くなります。

### 実行方法

```
運営仕様書のパスリスト(specs.csv)からマスタデータを差分生成してください。

パラメータ:
- release_key: 202601010
- specs_csv_path: domain/raw-data/masterdata/released/202601010/specs/specs.csv
- mode: incremental
- past_data_dir: domain/raw-data/masterdata/released/202601000/tables
```

### 動作フロー

1. **スキル実行**: 運営仕様書から該当する機能スキルを実行
2. **生成テーブルの記録**: 生成されたテーブル名を記録
3. **未生成テーブルの継承**: 過去データから未生成テーブルをコピー
4. **変更履歴の記録**: 各テーブルの変更状況を記録

### 差分検出ロジック（TypeScript擬似コード）

```typescript
/**
 * 過去データと正解データの差分を検出
 */
function detectChangedTables(
  pastDataDir: string,
  correctDataDir: string
): {
  changed: string[],
  unchanged: string[]
} {
  const allTables = getAllTableNames()
  const changed: string[] = []
  const unchanged: string[] = []

  for (const tableName of allTables) {
    const pastData = readCSV(path.join(pastDataDir, tableName))
    const correctData = readCSV(path.join(correctDataDir, tableName))

    if (areEqual(pastData, correctData)) {
      unchanged.push(tableName)
    } else {
      changed.push(tableName)
    }
  }

  return { changed, unchanged }
}

/**
 * 選択的生成モード
 */
async function generateMasterdata(
  mode: "full" | "incremental",
  pastDataDir?: string
) {
  if (mode === "incremental" && !pastDataDir) {
    throw new Error("Incremental mode requires past data directory")
  }

  const generatedTables = new Set<string>()

  // スキル実行
  for (const skill of skills) {
    const tables = await executeSkill(skill)
    tables.forEach(t => generatedTables.add(t))
  }

  // 差分生成モードの場合、未生成テーブルは過去データから継承
  if (mode === "incremental") {
    const allTables = getAllTableNames()
    for (const tableName of allTables) {
      if (!generatedTables.has(tableName)) {
        await copyFromPastData(tableName, pastDataDir)
        console.log(`継承: ${tableName} (運営仕様書に記載なし)`)
      }
    }
  }
}
```

### バージョン管理

```typescript
interface TableVersion {
  tableName: string
  releaseKey: string
  changeType: "created" | "updated" | "unchanged"
  changedFields?: string[]
  rowsAdded?: number
  rowsDeleted?: number
  rowsModified?: number
}

/**
 * テーブルのバージョン履歴を記録
 */
function recordTableVersion(
  tableName: string,
  releaseKey: string,
  pastData: any[],
  currentData: any[]
): TableVersion {
  const diff = calculateDiff(pastData, currentData)

  return {
    tableName,
    releaseKey,
    changeType: diff.totalChanges === 0 ? "unchanged" : "updated",
    changedFields: diff.changedFields,
    rowsAdded: diff.additions.length,
    rowsDeleted: diff.deletions.length,
    rowsModified: diff.modifications.length
  }
}
```

### 期待効果

- **ファイル生成率: 100%達成**（未生成テーブルは過去データから継承）
- **運営負荷の軽減**: 変更があったテーブルのみ運営仕様書に記載
- **データ整合性の向上**: 過去データとの連続性を保証

### 使用例

#### 全生成モード（従来）
```
masterdata-from-bizops-all --mode=full
```
→ 運営仕様書から生成できるテーブルのみ作成

#### 差分生成モード（新規）
```
masterdata-from-bizops-all --mode=incremental --past-data=domain/raw-data/masterdata/released/202601000/tables
```
→ 運営仕様書から生成 + 未生成テーブルは過去データから継承
