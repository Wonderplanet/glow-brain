# マスタデータ生成精度改善 実装プロンプト集

このファイルには、改善提案を実行するための具体的なプロンプトが含まれています。

---

## フェーズ1: クイックウィン（1-2週間）

### プロンプト1-1: スキル呼び出しトリガーロジックの改善【最優先】

```
masterdata-from-bizops-allスキルのスキル呼び出しトリガーロジックを改善してください。

## 背景
現在、14個の子スキル中6個しか実行されておらず、7個のスキル（hero, ingame, mission, gacha, enemy-autoplayer, artwork, pvp）が実行されていません。これが生成率32.1%の根本原因です。

## 問題
運営仕様書は90%のテーブルをカバーしているにもかかわらず、masterdata-from-bizops-allが運営仕様書から各スキルを認識・呼び出すロジックに不備があります。

## 改善内容

### 1. 運営仕様書の解析ロジック強化
以下の情報源を複合的に活用してスキルを呼び出すようにしてください：
- ファイル名パターン（例: `ヒーロー基礎設計_*` → hero）
- シート名パターン（例: `ガチャ設計書_*` → gacha）
- ファイル内容のキーワード（例: `ミッション名`, `ミッション達成条件` → mission）
- specs.csvのメタデータ（ファイルタイプ、カテゴリ情報）

### 2. スキル呼び出しルールの明確化
各スキルに対して、以下の形式でトリガールールを定義してください：

```javascript
const SKILL_TRIGGERS = {
  "hero": [
    {type: "filename", pattern: /ヒーロー基礎設計_/},
    {type: "sheet", pattern: /キャラクター|ユニット|Hero/},
    {type: "content", keywords: ["unit_id", "attack_id", "ability_id"]}
  ],
  "gacha": [
    {type: "filename", pattern: /ガチャ設計書_|ガシャ/},
    {type: "sheet", pattern: /ガチャ|Gacha/},
    {type: "content", keywords: ["prize_id", "gacha_upper"]}
  ],
  "mission": [
    {type: "filename", pattern: /ミッション/},
    {type: "sheet", pattern: /ミッション|Mission/},
    {type: "content", keywords: ["mission_id", "達成条件", "報酬"]}
  ],
  "ingame": [
    {type: "filename", pattern: /インゲーム|クエスト設計/},
    {type: "sheet", pattern: /InGame|ゲーム内/},
    {type: "content", keywords: ["mst_in_game_id", "background_asset_key"]}
  ],
  "enemy-autoplayer": [
    {type: "filename", pattern: /敵|Enemy/},
    {type: "sheet", pattern: /敵キャラ|Enemy/},
    {type: "content", keywords: ["enemy_character_id", "enemy_outpost_id"]}
  ],
  "artwork": [
    {type: "filename", pattern: /原画|アートワーク/},
    {type: "sheet", pattern: /原画|Artwork/},
    {type: "content", keywords: ["artwork_id", "fragment_id"]}
  ],
  "pvp": [
    {type: "filename", pattern: /ランクマッチ|PvP/},
    {type: "sheet", pattern: /ランクマッチ|PVP/},
    {type: "content", keywords: ["mst_pvp_id"]}
  ]
}
```

### 3. フォールバック機構の追加
- トリガーマッチしなかったファイルは全スキルに通知
- 各スキルが自己判定で処理可否を判断
- 処理されなかったファイルは警告レポートに記録

## 対象ファイル
`.claude/skills/masterdata-from-bizops-all/skill.ts`（または該当するメインファイル）

## 期待効果
- スキル実行率: 43% → 100%
- ファイル生成率: 32.1% → 85%以上
- 未生成ファイル: 53個 → 6個以下

## 検証方法
リリースキー202601010のデータで再度生成を実行し、以下を確認：
1. 14個のスキルすべてが実行されたか
2. hero, mission, gacha等のテーブルが生成されたか
3. 生成率が85%以上になったか

## 参考資料
- 改善提案レポート: `domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010/improvement-proposals/comprehensive_improvement_proposal.md`
- 未生成ファイル分析: `domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010/analysis/missing_files_analysis.md`
- 運営仕様書カバレッジ分析: spec-analyzerの結果を参照
```

---

### プロンプト1-2: 部分実行スキルの改善

```
quest-stage, advent-battle, shop-packの3つのスキルで一部テーブルが生成されていない問題を修正してください。

## 背景
これらのスキルは実行されているが、一部のテーブル（合計6ファイル）が生成されていません。

## 改善内容

### 1. quest-stage スキルの改善（3テーブル未生成）

**対象ファイル**: `.claude/skills/masterdata-from-bizops-quest-stage/`

**未生成テーブル**:
- MstQuestBonusUnit
- MstQuestEventBonusSchedule
- MstStageEndCondition

**改善策**:
- `MstQuestBonusUnit`: 運営仕様書の「ボーナスキャラ設定」「コイン獲得ボーナス」セクションを認識して生成
- `MstQuestEventBonusSchedule`: 運営仕様書の「ボーナス期間」「開催期間」セクションから期間情報を抽出
- `MstStageEndCondition`: クエスト設計シートの「クリア条件」カラムを認識して生成

### 2. advent-battle スキルの改善（1テーブル未生成）

**対象ファイル**: `.claude/skills/masterdata-from-bizops-advent-battle/`

**未生成テーブル**:
- MstEventBonusUnit

**改善策**:
- 運営仕様書の「コイン獲得ボーナスキャラ」セクションを認識
- 降臨バトル用のボーナスキャラ設定を生成

### 3. shop-pack スキルの改善（2テーブル未生成）

**対象ファイル**: `.claude/skills/masterdata-from-bizops-shop-pack/`

**未生成テーブル**:
- OprProduct
- OprProductI18n

**改善策**:
- パック作成時に対応するストア商品（OprProduct）も自動生成
- 商品IDとパックIDの関連付けを明確に管理
- I18nテーブルも自動生成

## 期待効果
- ファイル生成率: 85% → 92%以上（6ファイル追加）

## 検証方法
リリースキー202601010のデータで再度生成を実行し、以下のファイルが生成されているか確認：
- MstQuestBonusUnit.csv
- MstQuestEventBonusSchedule.csv
- MstStageEndCondition.csv
- MstEventBonusUnit.csv
- OprProduct.csv
- OprProductI18n.csv
```

---

### プロンプト1-3: ID生成ロジックの統一

```
マスタデータのID生成ロジックを統一し、命名規則・採番位置の誤りを修正してください。

## 背景
ID命名規則・採番位置の誤りにより、5ファイルで大規模な差分が発生しています。

## 問題事例
1. 報酬IDの末尾番号: `_01, _02`ではなく`_1, _2`形式であるべき
2. ID採番位置: 既存データの最大IDを調査せずに採番している
3. アセットキー: イベントIDとの対応関係が不明確

## 改善内容

### 1. ID採番の自動化

各スキルに以下の関数を追加してください：

```typescript
/**
 * 既存データから次のIDを取得
 */
function getNextId(tableName: string, idColumn: string, pastDataDir: string): number {
  const csvPath = path.join(pastDataDir, `${tableName}.csv`)
  if (!fs.existsSync(csvPath)) {
    return 1 // 過去データがない場合は1から開始
  }

  const existingData = parseCSV(csvPath)
  const maxId = Math.max(...existingData.map(row => extractNumber(row[idColumn])))
  return maxId + 1
}

/**
 * IDから数値部分を抽出
 */
function extractNumber(id: string): number {
  const match = id.match(/(\d+)$/)
  return match ? parseInt(match[1]) : 0
}
```

### 2. ID命名規則の明確化

`references/id_naming_rules.md`を作成し、以下のルールを明記：

#### 報酬系テーブル
- 末尾番号: ゼロ埋めなし（`_1`, `_2`, ...）
- 例: `quest_raid_jig1_reward_group_00001_01_1`

#### イベント系テーブル
- リリースキーベース（`event_jig_00001`）
- 例: `event_jig_00001`, `event_jig_00002`

#### アセットキー
- イベントIDから生成（`event_jig_00001` → `jig_00001`）
- 背景アセットキー: クエストタイプ別（`charaget01` → `jig_00003`）

#### ステージID
- 連番: ゼロ埋め5桁（`00001`, `00002`, ...）
- 例: `event_jig1_charaget01_00001`

### 3. 対象スキル
以下のスキルでID生成ロジックを修正：
- masterdata-from-bizops-reward
- masterdata-from-bizops-quest-stage
- masterdata-from-bizops-advent-battle
- masterdata-from-bizops-shop-pack

## 期待効果
- 差分率: 68% → 40%以下（ID関連の差分を解消）

## 検証方法
リリースキー202601010のデータで再度生成を実行し、以下を確認：
1. MstAdventBattleReward.csvのIDが`_1`, `_2`形式になっているか
2. MstStageEventReward.csvのID範囲が正解データと一致しているか
3. アセットキーが`event_jig1_*`形式になっているか
```

---

## フェーズ2: 中期改善（1-2ヶ月）

### プロンプト2-1: データ依存関係の自動管理

```
親テーブル作成時に子テーブルを自動生成する機能を実装してください。

## 背景
親テーブル未生成により子テーブルが連鎖的に欠落する問題が発生しています（6ファイル影響）。

## 改善内容

### 1. テーブル依存関係の定義

`config/table_dependencies.json`を作成：

```json
{
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
```

### 2. 親テーブル作成時の自動連携

各スキルに以下のロジックを追加：

```typescript
/**
 * 親テーブル作成後、自動的に子テーブルも生成
 */
async function createWithDependencies(parentTable: string, parentData: any[]) {
  // 親テーブル生成
  await writeCSV(parentTable, parentData)

  // 依存関係を確認
  const dependencies = TABLE_DEPENDENCIES[parentTable]
  if (!dependencies) return

  // 子テーブル自動生成
  for (const childTable of dependencies) {
    if (childTable.endsWith("I18n")) {
      // I18nテーブルは親から自動生成
      const childData = parentData.map(row => ({
        id: `${row.id}_ja`,
        name: row.name || "",
        description: row.description || "",
        // 他のI18nフィールド
      }))
      await writeCSV(childTable, childData)
    } else if (childTable === "MstPackContent") {
      // PackContentは別途生成ロジック
      // 運営仕様書から内容物を抽出
    }
    // 他の子テーブルも同様に処理
  }
}
```

### 3. 整合性チェックの強化

```typescript
/**
 * テーブル間の整合性をチェック
 */
function validateTableIntegrity(allTables: Map<string, any[]>): ValidationResult[] {
  const errors: ValidationResult[] = []

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

## 期待効果
- 差分率: 40% → 25%以下（依存関係の差分を解消）

## 検証方法
1. MstPackを生成した際、MstPackContentとMstPackI18nも自動生成されるか
2. MstStoreProductを生成した際、MstStoreProductI18nも自動生成されるか
3. 孤立レコードが検出されるか
```

---

### プロンプト2-2: アセットキー・パラメータ推測精度の向上

```
アセットキー生成ルールを明確化し、パラメータ推測精度を向上させてください。

## 背景
アセットキーの推測誤り（4ファイル影響）、パラメータ計算の不正確（3ファイル影響）が発生しています。

## 改善内容

### 1. アセットキー生成ルールの明確化

`references/asset_key_rules.md`を作成：

#### イベントID → アセットキープレフィックス
- `event_jig_00001` → `jig_`
- `event_sur1_00001` → `sur1_`

#### クエストタイプ → 背景アセットキー
- `charaget01`（キャラ取得クエスト1） → `jig_00003`
- `charaget02`（キャラ取得クエスト2） → `jig_00002`
- `1day`（1日1回クエスト） → `jig_00001`

実装例：

```typescript
function generateAssetKey(eventId: string, questType?: string): string {
  // イベントIDからプレフィックス抽出
  const match = eventId.match(/event_(\w+)_/)
  if (!match) throw new Error(`Invalid event ID: ${eventId}`)

  const prefix = match[1] // "jig", "sur1" など

  if (questType) {
    // クエストタイプ別の背景アセットキー
    const QUEST_ASSET_MAP = {
      "charaget01": `${prefix}_00003`,
      "charaget02": `${prefix}_00002`,
      "1day": `${prefix}_00001`,
      "story": `${prefix}_00001`,
      "challenge": `${prefix}_00002`,
      "high_difficulty": `${prefix}_00003`
    }
    return QUEST_ASSET_MAP[questType] || `${prefix}_00001`
  }

  return `${prefix}_00001`
}
```

### 2. パラメータ推測の高度化

過去データのパターン分析により推測精度を向上：

```typescript
/**
 * 過去データから類似パターンを学習
 */
function learnParameterPatterns(pastData: any[]): ParameterPattern {
  // ステージ進行に応じたパラメータ増加率を分析
  const stageProgression = analyzeStageProgression(pastData)

  return {
    costStamina: {
      baseValue: 5,
      increaseRate: 0.5, // ステージごとに0.5ずつ増加
      max: 20
    },
    coin: {
      baseValue: 100,
      increaseRate: 50,
      max: 1000
    },
    recommendedLevel: {
      baseValue: 10,
      increaseRate: 5,
      max: 100
    }
  }
}

/**
 * 学習したパターンから推測値を生成
 */
function estimateParameter(
  parameterName: string,
  stageNumber: number,
  pattern: ParameterPattern
): number {
  const config = pattern[parameterName]
  const estimated = config.baseValue + (stageNumber - 1) * config.increaseRate
  return Math.min(estimated, config.max)
}
```

### 3. 推測値レポートの詳細化

推測根拠と信頼度スコアを追加：

```typescript
interface InferenceReport {
  field: string
  value: any
  confidence: "High" | "Medium" | "Low"
  reasoning: string
  source: "past_data_pattern" | "specification" | "default_value" | "manual_input_required"
}

// 使用例
const report: InferenceReport = {
  field: "cost_stamina",
  value: 10,
  confidence: "Medium",
  reasoning: "過去データのステージ2の平均値10を使用（類似イベント3件の平均）",
  source: "past_data_pattern"
}
```

## 期待効果
- 差分率: 25% → 10%以下（推測誤差を削減）

## 検証方法
1. MstStage.csvのasset_keyが`event_jig1_*`形式になっているか
2. cost_stamina, coin, recommended_levelの値が正解データに近いか
3. 推測値レポートに根拠と信頼度が記載されているか
```

---

### プロンプト2-3: 運営仕様書テンプレートの整備

```
運営仕様書のテンプレートとチェックリストを作成し、記載品質を向上させてください。

## 背景
運営仕様書の記載内容が不足・不明確で、スキルが正しく解釈できない箇所があります（7ファイル影響）。

## 改善内容

### 1. 機能別テンプレートの作成

`templates/bizops-specs/`ディレクトリに以下のテンプレートを作成：

#### ヒーロー追加テンプレート（`hero_template.xlsx`）

必須シート：
- 基礎情報（unit_id, name, rarity, role等）
- アビリティ（ability_id, name, description, effect等）
- 攻撃（attack_id, name, damage, element等）
- 必殺技（special_attack_id, name, description等）
- グレード強化（rank_up素材一覧）
- セリフ（speech_balloon一覧）

#### ガチャ追加テンプレート（`gacha_template.xlsx`）

必須シート：
- ガチャ基本情報（gacha_id, name, start_date, end_date等）
- 景品ラインナップ（unit_id, rarity, weight等）
- 天井設定（upper_count, guaranteed_prize等）
- 消費リソース（resource_type, amount等）
- ピックアップキャラ訴求文（display_unit_id, message等）

#### ミッション追加テンプレート（`mission_template.xlsx`）

必須シート：
- ミッション一覧（mission_id, name, description, condition等）
- 達成条件詳細（condition_type, target, required_value等）
- 報酬一覧（reward_type, resource_id, amount等）
- 依存関係（depends_on_mission_id等）
- ログインボーナス（daily_bonus_schedule等）

### 2. チェックリストの作成

`checklists/bizops-specs-checklist.md`を作成：

```markdown
# 運営仕様書チェックリスト

## ヒーロー追加時
- [ ] ユニットID（unit_id）が記載されているか
- [ ] キャラクター名（日本語・英語）が記載されているか
- [ ] レアリティ（rarity）が記載されているか
- [ ] ロール（role: Tank/Attacker/Healer等）が記載されているか
- [ ] アビリティが最低1つ記載されているか
- [ ] 攻撃データ（通常攻撃・必殺技）が記載されているか
- [ ] グレード強化素材が記載されているか
- [ ] セリフ（最低5種類）が記載されているか

## ガチャ追加時
- [ ] ガチャID（gacha_id）が記載されているか
- [ ] ガチャ名が記載されているか
- [ ] 開催期間（start_date, end_date）が記載されているか
- [ ] 景品ラインナップ（全キャラ）が記載されているか
- [ ] 各景品の出現確率（weight）が記載されているか
- [ ] 天井設定（回数、保証内容）が記載されているか
- [ ] 消費リソース（プリズム等）と量が記載されているか
- [ ] ピックアップキャラの訴求文が記載されているか

## ミッション追加時
- [ ] ミッションID（mission_id）が記載されているか
- [ ] ミッション名が記載されているか
- [ ] 達成条件が具体的に記載されているか
- [ ] 報酬内容（種類・量）が記載されているか
- [ ] 依存関係（前提ミッション）が記載されているか
- [ ] 開催期間が記載されているか
- [ ] ログインボーナスのスケジュールが記載されているか
```

### 3. 運営仕様書検証ツールの作成

`scripts/validate-bizops-specs.ts`を作成：

```typescript
/**
 * 運営仕様書の必須項目をチェック
 */
function validateBizOpsSpecs(specFile: string, specType: string): ValidationResult {
  const data = parseSpreadsheet(specFile)
  const errors: string[] = []

  const REQUIRED_FIELDS = {
    "hero": ["unit_id", "name", "rarity", "role", "ability_id", "attack_id"],
    "gacha": ["gacha_id", "name", "start_date", "end_date", "prize_list", "upper_count"],
    "mission": ["mission_id", "name", "condition", "reward_list"]
  }

  const requiredFields = REQUIRED_FIELDS[specType] || []

  for (const field of requiredFields) {
    if (!hasField(data, field)) {
      errors.push(`必須項目が不足: ${field}`)
    }
  }

  return {
    isValid: errors.length === 0,
    errors,
    warnings: []
  }
}
```

## 期待効果
- 運営仕様書の品質向上
- スキルの解釈精度向上
- 差分率: 10% → 5%以下

## 検証方法
1. テンプレートを使って新規運営仕様書を作成
2. チェックリストで漏れがないか確認
3. 検証ツールで自動チェック
4. マスタデータ生成の精度が向上したか確認
```

---

## フェーズ3: 長期改善（3-6ヶ月）

### プロンプト3-1: 過去データ自動継承機能

```
運営仕様書に記載がないテーブルは過去データを自動継承する機能を実装してください。

## 背景
運営仕様書に記載がないテーブルも正解データには存在します。これらは過去データをそのまま使う必要があります。

## 改善内容

### 1. 差分検出ロジック

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
```

### 2. 選択的生成モード

masterdata-from-bizops-allに`--mode`オプションを追加：

```bash
# 全生成モード（現状）
masterdata-from-bizops-all --mode=full

# 差分生成モード（新規）
masterdata-from-bizops-all --mode=incremental --past-data=domain/raw-data/masterdata/released/202601010/past_tables
```

実装：

```typescript
async function generateMasterdata(mode: "full" | "incremental", pastDataDir?: string) {
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

### 3. バージョン管理の強化

各テーブルの変更履歴を管理：

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

## 期待効果
- ファイル生成率: 100%達成
- 運営負荷の軽減（変更箇所のみ記載）

## 検証方法
1. 差分生成モードで実行
2. 未生成テーブルが過去データから自動継承されるか
3. 変更履歴が正しく記録されるか
```

---

## 使用方法

各プロンプトをコピーして、Claude Codeに貼り付けて実行してください。

### 推奨順序

1. **フェーズ1を順番に実行**（1-2週間）
   - プロンプト1-1 → プロンプト1-2 → プロンプト1-3

2. **フェーズ1の検証**
   - リリースキー202601010で再生成
   - 生成率が92%以上になることを確認

3. **フェーズ2を順番に実行**（1-2ヶ月）
   - プロンプト2-1 → プロンプト2-2 → プロンプト2-3

4. **フェーズ2の検証**
   - 差分率が5%以下になることを確認

5. **フェーズ3の実行**（3-6ヶ月）
   - プロンプト3-1

---

## 注意事項

- 各プロンプトは独立しているため、順不同でも実行可能ですが、推奨順序での実行を推奨します
- 実装後は必ず検証を行い、期待効果が得られているか確認してください
- 問題が発生した場合は、改善提案レポートを参照して原因を特定してください
