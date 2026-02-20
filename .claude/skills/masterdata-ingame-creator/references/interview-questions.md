# Step 0 質問フロー（ユーザーインタビュー）

インゲームマスタデータを作成するために必要な情報を確認するための質問フローです。
**不足情報はまとめて1回だけ質問する。** ユーザーの入力を分析し、不足している項目のみ質問する。

---

## 確認する6項目

### 1. ステージ種別

| 種別 | キーワード例 | 説明 |
|------|------------|------|
| `event_charaget` | キャラゲット, 獲得クエスト | キャラクター獲得型イベントクエスト |
| `event_challenge` | チャレンジ, タイムアタック | チャレンジクエスト（SpeedAttack付き） |
| `event_savage` | サベージ | サベージバトル（高難易度） |
| `event_1day` | デイリー, 1日限定 | 1日限定イベントクエスト |
| `raid` | レイド | レイドバトル（スコアアタック型） |
| `normal` | 通常, フリクエ | 通常難易度クエスト |
| `hard` | ハード | ハード難易度クエスト |
| `veryhard` | VH, ベリーハード | ベリーハード難易度クエスト |

### 2. インゲームID

命名規則（[id-naming-rules.md](id-naming-rules.md)参照）に従い提案する。
ユーザーが明示しない場合は、以下の形式で提案する:

```
{種別}_{シリーズID}{番号}_{ステージ識別}_{連番5桁}
例: event_kai1_charaget01_00001
    event_kai1_challenge01_00001
    raid_kai1_00001
```

### 3. 使用する敵キャラ

- **既存の `MstEnemyCharacter.id`** を使用するか、新規作成するか
- DuckDBで確認:
  ```sql
  SELECT id FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE) WHERE id LIKE '%{シリーズ}%';
  ```
- プレイヤーキャラが敵として登場する場合は `chara_{シリーズ}_{番号}` 形式
- 敵専用キャラの場合は `enemy_{シリーズ}_{番号}` 形式

### 4. ボスの有無

- ボスが **ある** 場合:
  - ボスキャラID（`mst_enemy_character_id`）
  - ボスの色属性（Colorless/Red/Blue/Yellow/Green）
  - `character_unit_kind` → `Boss` か `AdventBattleBoss` か
- ボスが **ない** 場合: MstInGame.boss_mst_enemy_stage_parameter_id は空

### 5. コマ効果の有無

- 指定がなければ `None`（エフェクトなし）で進める
- 特定コマ効果を使いたい場合:
  - AttackPowerDown（敵の攻撃力ダウン）
  - Darkness（暗闇）
  - Poison（毒）
  - Burn（燃焼）
  - Gust（吹き飛ばし）
  - その他（MstKomaLineの全KomaEffectType参照）

### 6. 特別ルール（MstInGameSpecialRule）

以下が必要かどうか確認:
- **SpeedAttack**: チャレンジ/サベージの場合、クリアタイム制限（ms）を設定
- **NoContinue**: コンティニュー禁止
- **PartySeries**: 使用シリーズ制限
- **TimeLimit**: バトル時間制限
- **その他**: OutpostHp上書き、PartyUnitNum制限等

---

## まとめ質問テンプレート

以下の情報が不足している場合に使用する質問テンプレート:

```
インゲームマスタデータを作成するために、以下を教えてください。

**未確認の項目のみ質問する:**

【ステージ種別】 event_charaget / event_challenge / event_savage / raid / normal / hard / veryhard のうちどれですか？

【使用する敵キャラ】
- 雑魚敵のキャラID（例: chara_kai_00101, enemy_kai_00001）
- ボスのキャラID（例: chara_kai_00201）

【ボスの設定】
- ボスの色属性: Colorless / Red / Blue / Yellow / Green
- ボスが変身する場合は変身後のキャラIDと変身HP%

【特別ルール（チャレンジ/サベージの場合）】
- SpeedAttack の制限時間（ms単位）
- コンティニュー禁止の有無
- シリーズ制限の有無
```

---

## 設計確認サマリーテンプレート

CSV生成前にユーザー承認を取るためのサマリー:

```markdown
## 設計確認サマリー

以下の設計でインゲームマスタデータを生成します。問題なければ「OK」と入力してください。

### 基本設定
- **インゲームID**: `{id}`
- **ステージ種別**: {種別}
- **ステージ説明**: {ユーザーの意図をまとめた1〜2文}

### 敵構成
| 種別 | キャラID | 色 | HP目安 |
|------|---------|-----|-------|
| ボス | `{キャラID}` | {色} | {HP} |
| 雑魚A | `{キャラID}` | {色} | {HP} |
| 雑魚B | `{キャラID}` | {色} | {HP} |

### コマ設定
- 行数: {N}行
- コマ効果: {効果種別 or "なし（None）"}

### シーケンス概要
- ボス: {InitialSummon / ElapsedTime}で出現、砦付近に配置
- 雑魚A: {条件}で{N}体出現
- グループ切り替え: {有/無}

### 特別ルール
- {ルール or "なし"}

### 生成するCSVファイル
1. MstEnemyStageParameter（{N}行）
2. MstEnemyOutpost（1行）
3. MstPage（1行）
4. MstKomaLine（{N}行）
5. MstAutoPlayerSequence（{N}行）
6. MstInGame（1行）
7. MstStage（{N}行）
8. MstStageEventSetting（{N}行）
9. MstStageEventReward（{N}行）
{10. MstInGameSpecialRule（{N}行）} ※オプション
{11. MstStageClearTimeReward（{N}行）} ※オプション
```
