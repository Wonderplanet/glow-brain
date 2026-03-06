# 期間限定ミッション 要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstMissionLimitedTerm.csv`（期間限定ミッション定義・N行、既存CSVへの追記）
> - `MstMissionLimitedTermI18n.csv`（多言語説明文・N行、既存CSVへの追記）
> - `MstMissionReward.csv`（報酬レコード・N行、既存CSVへの追記）

---

## テンプレート

```
# 期間限定ミッション 要件テキスト

## 基本情報

- リリースキー: {このリリースのリリースキーを記入}
  例: 202603010
- 対象コンテンツ: {降臨バトル / アートワークパネル / ダンジョン}
  例: 降臨バトル
- 降臨バトルのステージ名（description に使用）: {プレイヤーに表示されるステージ名}
  例: 片鱗を示す
- イベントキー（group_id のプレフィックスに使用）: {event_id から {key} 部分を記入}
  例: hut_00001  ← event_hut_00001 の "hut_00001" 部分

## 期間

- 開始: YYYY-MM-DD HH:MM （JST）
- 終了: YYYY-MM-DD HH:MM （JST）

  ※ 開始時刻の慣例: 降臨バトル開始日の 00:00 JST
  ※ 終了時刻の慣例: 降臨バトル終了日の 23:59:59 JST（深夜終了が多い。イベントによっては 19:59:59 JST）

## 達成条件（挑戦回数）

{N}回挑戦: {報酬アイテム名} × {数量}

（1ミッションにつき1報酬が標準。標準パターンは 5回 / 10回 / 20回 / 30回 の4段階）

---
【記入欄】
5回挑戦:
10回挑戦:
20回挑戦:
30回挑戦:
（回数・段階数が異なる場合はそのまま書き換えてください）
```

---

## 報酬アイテム名の選択肢と対応ID

Claudeがこのテキストを解釈してIDに変換します。以下の表記を使ってください。

### 通貨・汎用リソース（標準パターン）

実データに基づく標準的な報酬構成は以下の通りです。5回・20回はコイン、10回・30回は無償ダイヤが慣例です。

| 表記 | 報酬タイプ | 備考 |
|------|-----------|------|
| コイン | `Coin` | resource_id 不要 |
| プリズム | `FreeDiamond` | 無償ダイヤ。resource_id 不要 |

### チケット類

| 表記 | アイテムID | 備考 |
|------|-----------|------|
| ピックアップガシャチケット | `ticket_glo_00003` | ピックアップ対象を引ける |
| スペシャルガシャチケット | `ticket_glo_00002` | どのガシャでも使える |

### カラーメモリー（Lv上限開放素材）

| 表記 | アイテムID | 属性 |
|------|-----------|------|
| カラーメモリー・グレー | `memory_glo_00001` | 無属性 |
| カラーメモリー・レッド | `memory_glo_00002` | 赤 |
| カラーメモリー・ブルー | `memory_glo_00003` | 青 |
| カラーメモリー・イエロー | `memory_glo_00004` | 黄 |
| カラーメモリー・グリーン | `memory_glo_00005` | 緑 |

### メモリーフラグメント（Lv上限開放素材・色共通）

| 表記 | アイテムID | レアリティ |
|------|-----------|-----------|
| メモリーフラグメント・初級 | `memoryfragment_glo_00001` | SR相当 |
| メモリーフラグメント・中級 | `memoryfragment_glo_00002` | SSR相当 |
| メモリーフラグメント・上級 | `memoryfragment_glo_00003` | UR相当 |

> **その他アイテム**: 上記にない場合はアイテムIDを直接記入するか、「アイテム名（item_xxx_xxxxx）× N個」と書く。

---

## 記入済みサンプル（実データ: 202603010 リリースより）

```
# 期間限定ミッション 要件テキスト

## 基本情報

- リリースキー: 202603010
- 対象コンテンツ: 降臨バトル
- 降臨バトルのステージ名: 片鱗を示す
- イベントキー: hut_00001

## 期間

- 開始: 2026-03-07 00:00 JST
- 終了: 2026-03-16 19:59:59 JST

## 達成条件（挑戦回数）

5回挑戦:  コイン × 2000
10回挑戦: プリズム × 20
20回挑戦: コイン × 3000
30回挑戦: プリズム × 30
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、期間限定ミッションのマスタデータCSVを生成してください。

【生成対象】
- MstMissionLimitedTerm.csv（追記N行。既存の最大 limited_term_N の続き番号から採番）
- MstMissionLimitedTermI18n.csv（追記N行。上記 limited_term ID に対応）
- MstMissionReward.csv（追記N行。既存の最大 mission_reward_N の続き番号から採番）

【ID採番】
- MstMissionLimitedTerm の id は、既存CSVの最大連番+1から採番してください。
- progress_group_key は、既存CSVの最大グループ番号（group{N}）の続き番号を使用してください。
- MstMissionReward の id は、既存CSVの最大連番+1から採番してください。

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係

```
MstMissionLimitedTerm（N行 = 達成段階数）
  ├─ id: limited_term_{連番}（全リリース通算の連番）
  ├─ release_key: リリースキー
  ├─ progress_group_key: group{連番}（同グループのミッション群を識別）
  ├─ criterion_type: AdventBattleChallengeCount（固定）
  ├─ criterion_value: （空）
  ├─ criterion_count: 5 / 10 / 20 / 30（挑戦回数）
  ├─ mission_category: AdventBattle（固定）
  ├─ reset_type: Revival（デフォルト固定）
  ├─ mst_mission_reward_group_id: {イベントキー}_limited_term_{1〜N}
  ├─ sort_order: 1 〜 N（段階順）
  ├─ destination_scene: AdventBattle（固定）
  ├─ start_at: 開始日時（UTC = JST - 9時間）
  └─ end_at: 終了日時（UTC = JST - 9時間）
    ↓
MstMissionLimitedTermI18n（N行 = MstMissionLimitedTerm と同数）
  ├─ id: {limited_term_id}_ja
  ├─ release_key: リリースキー
  ├─ mst_mission_limited_term_id: 対応する limited_term_id
  ├─ language: ja（固定）
  └─ description: 降臨バトル「{ステージ名}」に{N}回挑戦しよう！
    ↓
MstMissionReward（N行 = MstMissionLimitedTerm と同数）
  ├─ id: mission_reward_{連番}（全テーブル通算の連番）
  ├─ release_key: リリースキー
  ├─ group_id: {イベントキー}_limited_term_{1〜N}（MstMissionLimitedTerm の mst_mission_reward_group_id と一致）
  ├─ resource_type: Coin / FreeDiamond / Item / ...
  ├─ resource_id: アイテムID（Coin / FreeDiamond は空）
  ├─ resource_amount: 数量
  ├─ sort_order: 1（固定）
  └─ 備考: 降臨バトル「{ステージ名}」に{N}回挑戦しよう！
```

### 標準的な4段階の生成例（イベントキー: xxx_00001）

| limited_term_id | sort_order | criterion_count | mst_mission_reward_group_id |
|----------------|------------|-----------------|------------------------------|
| limited_term_N   | 1 | 5  | xxx_00001_limited_term_1 |
| limited_term_N+1 | 2 | 10 | xxx_00001_limited_term_2 |
| limited_term_N+2 | 3 | 20 | xxx_00001_limited_term_3 |
| limited_term_N+3 | 4 | 30 | xxx_00001_limited_term_4 |

---

## イベントミッション（MstMissionEvent）との違い

| 比較項目 | 期間限定ミッション | イベントミッション |
|---------|-----------------|-----------------|
| テーブル | `MstMissionLimitedTerm` | `MstMissionEvent` |
| 紐づけ方法 | 期間（start_at / end_at）で有効期間を管理 | イベントID（mst_event_id）で紐づけ |
| 典型的な達成条件 | 降臨バトルに N 回挑戦 | 特定キャラのグレードアップ・レベル上げ |
| criterion_type の例 | `AdventBattleChallengeCount` | `SpecificUnitGradeUpCount`, `SpecificUnitLevel` |
| criterion_value | 基本的に空（対象限定なし） | 対象キャラID（例: `chara_kai_00601`）など |
| 段階数 | 4段階固定（5・10・20・30回） | イベントによって異なる（5〜10項目以上も） |
| reset_type | Revival / Monthly | なし（イベント終了で無効） |
| 遷移先 | `AdventBattle` 固定 | `UnitList` など |

---

## 達成条件タイプ（criterion_type）の一覧

実データから確認できる値と、DBスキーマ上定義されているカテゴリー：

| criterion_type | 意味 | 対象カテゴリー |
|----------------|------|---------------|
| `AdventBattleChallengeCount` | 降臨バトルへの挑戦回数 | `AdventBattle` |

> **注**: 現時点ではすべての期間限定ミッションが `AdventBattleChallengeCount` を使用。
> DBスキーマ上は `mission_category` に `ArtworkPanel`・`Dungeon` も定義されているが、
> 対応する `criterion_type` は実データでは未使用。

---

## 注意事項

- **時刻はすべて JST で記入する**: Claudeが UTC（JST - 9時間）へ変換してCSVに出力します。変換不要です。
- **limited_term_N の連番は全リリース通算**: 同じ番号を使い回さないこと。既存CSVの最大値を必ず確認してから採番する。
- **progress_group_key も全リリース通算**: `group1`, `group2`... と連番で採番。既存の最大番号の次を使う。
- **mission_reward_N の連番も全リリース通算**: 既存CSVの最大値の続きから採番する。
- **同一リリースで複数の降臨バトルがある場合**: それぞれ別の `progress_group_key` を割り当て、`limited_term_id` を続き番号で採番する（例: 202511010 では group4 と group5 の2グループ）。
- **MstMissionLimitedTermDependency は通常不要**: 段階的開放（前のミッション達成後に次が解放される）が必要な場合のみ別途設定する。これまでの実データでは使用実績なし。
- **reset_type**: 通常は `Revival`（復刻時にリセット）のため記入不要。月次リセットが必要な特殊ケースのみ `Monthly` を指定する。
