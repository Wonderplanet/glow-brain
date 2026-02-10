---
name: masterdata-from-bizops-enemy-autoplayer
description: 敵・自動行動の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 5個(MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstEnemyOutpost, MstAutoPlayerSequence)。敵キャラと自動行動パターンのマスタデータを精度高く作成します。
---

# 敵・自動行動 マスタデータ作成スキル

## 概要

敵キャラクターと自動行動パターンの運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の5テーブルを自動生成:

**敵キャラクター基本情報**:
- **MstEnemyCharacter** - 敵キャラクターの基本情報(ユニットIDとの紐付け)
- **MstEnemyCharacterI18n** - 敵キャラクター名(多言語対応)

**敵のステージ別パラメータ**:
- **MstEnemyStageParameter** - ステージごとの敵のステータス(HP、攻撃力、移動速度等、19カラム)

**敵拠点情報**:
- **MstEnemyOutpost** - 敵拠点(アウトポスト)の設定

**自動行動パターン**:
- **MstAutoPlayerSequence** - 敵の召喚タイミング、行動パターン、条件分岐等(34カラム)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai) | `jig` |
| **quest_id** | クエストID | `event_jig1_charaget01` |
| **stage_id** | ステージID | `event_jig1_charaget01_00001` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
敵・自動行動の運営仕様書からマスタデータを作成してください。

添付ファイル:
- クエスト設計書_地獄楽_共闘関係編.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- quest_id: event_jig1_charaget01
- stage_id: event_jig1_charaget01_00001
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- クエストID、ステージID
- 出現する敵キャラクターのリスト(プレイアブル/敵専用の区別)
- 各敵の役割(Normal、Boss)と属性(Red、Blue、Green、Yellow、Colorless)
- 敵のステータス(HP、攻撃力、移動速度、ノックバック耐性等)
- 敵拠点の設定(HP、ダメージ無効化フラグ、アートワーク)
- 敵の召喚タイミング(初期配置、経過時間、条件トリガー)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstEnemyCharacter** - プレイアブルキャラの敵バージョンの場合のみ作成
2. **MstEnemyCharacterI18n** - 敵キャラクター名(多言語対応)
3. **MstEnemyStageParameter** - ステージごとの敵のステータス(19カラム)
4. **MstEnemyOutpost** - 敵拠点の設定
5. **MstAutoPlayerSequence** - 敵の召喚タイミングと行動パターン(34カラム)

#### ID採番ルール

```
MstEnemyCharacter.id: {mst_unit_id}(プレイアブル) または enemy_{series_id}_{連番5桁}(敵専用)
MstEnemyStageParameter.id: {base_character_id}_{quest_id}_{character_unit_kind}_{color}
MstEnemyOutpost.id: {quest_id}_{stage_number}
MstAutoPlayerSequence.id: {sequence_set_id}_{連番}
```

**例**:
```
chara_jig_00401 (プレイアブルキャラの敵バージョン)
c_jig_00401_jig1_charaget02_Boss_Red (敵ステージパラメータ)
event_jig1_charaget01_00001 (敵拠点)
event_jig1_charaget02_00001_1 (自動行動シーケンス)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか
- [ ] シーケンスの論理整合性(FriendUnitDeadの参照、SwitchSequenceGroupの参照等)

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstEnemyStageParameter.hp`: 設計書に記載なく推測
- `MstAutoPlayerSequence.action_type`: 設計書に記載なく推測
- `MstAutoPlayerSequence.summon_interval`: 設計書に記載なく推測

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- 以下の5シートを作成:
  1. MstEnemyCharacter
  2. MstEnemyCharacterI18n
  3. MstEnemyStageParameter(19カラム)
  4. MstEnemyOutpost
  5. MstAutoPlayerSequence(34カラム)

#### 2. 推測値レポート(必須)

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 注意事項

### 敵キャラクターの2つの役割

1. **プレイアブルキャラの敵バージョン** - MstEnemyCharacterを作成
2. **敵専用キャラクター** - MstEnemyCharacterを作成せず、直接MstEnemyStageParameterで定義

### MstAutoPlayerSequenceの複雑性

34カラムの詳細な設定が必要です。主要なカラム:
- condition_type: InitialSummon、ElapsedTime、OutpostHpPercentage等
- action_type: SummonEnemy、SwitchSequenceGroup
- 係数: enemy_hp_coef、enemy_attack_coef、enemy_speed_coef

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstEnemyStageParameter.mst_enemy_character_id` → `MstEnemyCharacter.id` または敵専用キャラID
- `MstAutoPlayerSequence.action_value`(SummonEnemyの場合) → `MstEnemyStageParameter.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: enum値のエラーが発生する

**対処法**:
enum値は**大文字小文字を正確に一致**させる。正しいenum値:
- character_unit_kind: Normal、Boss
- color: Colorless、Red、Blue、Yellow、Green
- condition_type: InitialSummon、ElapsedTime、OutpostHpPercentage等
- action_type: SummonEnemy、SwitchSequenceGroup

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます。
