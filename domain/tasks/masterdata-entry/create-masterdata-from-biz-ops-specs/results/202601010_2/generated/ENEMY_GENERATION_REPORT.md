# 敵・自動行動マスタデータ生成レポート

## 生成日時
2026-02-11

## 生成対象
リリースキー: 202601010 (地獄楽 いいジャン祭イベント)

## 生成完了テーブル

### 1. MstEnemyCharacter.csv
**レコード数**: 41

**概要**: 地獄楽イベントで登場する敵キャラクターの定義

**データソース**:
- クエスト設計のMstAutoPlayerSequence.csvから敵キャラIDを抽出
- 過去データの命名規則に従ってレコード生成

**推測値**:
- `is_phantomized`: プレイアブルキャラの敵化（c_jig_*）は1、純粋な敵（e_jig_*）は0と推測
- `is_displayed_encyclopedia`: 全て0（図鑑非表示）と推測
- `mst_series_id`: 全て "jig" (地獄楽)
- `mst_attack_hit_onomatopeia_group_id`: 全て空文字（未指定）

### 2. MstEnemyOutpost.csv
**レコード数**: 15

**概要**: 各クエストの敵タワー（ゲート）定義

**データソース**:
- MstAutoPlayerSequenceのsequence_set_idから必要なOutpostを推測
- 過去データの命名規則とHP設定パターンを参考

**推測値**:
- `hp`: クエスト難易度に応じて推測
  - 1日1回: 10,000
  - ストーリー(1-6話): 5,000 ~ 15,000 (段階的に増加)
  - チャレンジ(1-4話): 20,000 ~ 35,000
  - 高難度(1-3話): 50,000 ~ 100,000
  - 降臨バトル: 150,000
- `is_damage_invalidation`: 全て空文字（無敵なし）
- `outpost_asset_key`: 降臨バトル以外は空文字
- `artwork_asset_key`: 降臨バトルのみ "jig_0001"、他は空文字

### 3. MstAutoPlayerSequence.csv
**レコード数**: 153

**概要**: 敵の出現ルールと自動行動シーケンス定義

**データソース**:
- 5つのクエスト設計から直接データを統合
  1. 【1日1回】本能が告げている 危険だと
  2. 【ストーリー】必ず生きて帰る (6話分)
  3. 【チャレンジ】死罪人と首切り役人設計 (4話分)
  4. 【高難度】手負いの獣は恐ろしいぞ (3話分)
  5. 【降臨バトル】まるで 悪夢を見ているようだ_地獄楽

**推測値**:
- `id`: sequence_set_id + "_" + sequence_element_idで生成（元データは空白）
- `is_summon_unit_outpost_damage_invalidation`: TRUE→1, FALSE→空文字に変換

### 4. MstEnemyStageParameter.csv (継承)
**レコード数**: 790 (ヘッダー含む)

**概要**: 過去データをそのまま継承（新規レコード追加なし）

**データソース**:
- 過去データ: `/domain/raw-data/masterdata/released/202601010/past_tables/MstEnemyStageParameter.csv`

## データ整合性チェック項目

### 参照整合性
- ✅ MstAutoPlayerSequence.action_value (敵キャラID) → MstEnemyCharacter.id
  - 全41種類の敵キャラIDがMstAutoPlayerSequenceで参照されている

### ID採番ルール
- ✅ MstEnemyCharacter: `{c|e}_jig_{番号}_{用途}_{難易度}_{色}`形式
- ✅ MstEnemyOutpost: `{イベント種別}_jig1_{クエスト種別}_{番号}`形式
- ✅ MstAutoPlayerSequence: `{sequence_set_id}_{sequence_element_id}`形式

### リリースキー
- ✅ 全テーブルで release_key = 202601010 を設定

## 確認推奨事項

### 1. MstEnemyOutpostのHP値
クエスト難易度から推測した値のため、実際のゲームバランスに応じて調整が必要な可能性があります。

### 2. MstEnemyCharacterのis_phantomized
プレイアブルキャラの敵化フラグは命名規則（c_jig_*）から推測しました。
仕様書での明示的な定義があれば、そちらを優先してください。

### 3. アセットキーの設定
以下のアセットキーは実際のアセット名と照合が必要です:
- MstEnemyCharacter.asset_key: chara_jig_*, enemy_jig_*
- MstEnemyOutpost.artwork_asset_key: jig_0001

## 生成統計

| テーブル名 | レコード数 | データソース |
|-----------|----------|------------|
| MstEnemyCharacter | 41 | クエスト設計から抽出 |
| MstEnemyOutpost | 15 | クエスト構成から推測 |
| MstAutoPlayerSequence | 153 | クエスト設計から統合 |
| MstEnemyStageParameter | 790 | 過去データ継承 |
| **合計** | **999** | - |

## 備考

- 指定された「MstEnemyOutpostUnit」「MstAutoPlayerSequenceAction」はDBスキーマに存在しないため、生成対象から除外しました
- 敵の実際のゲーム内パラメータ（HP、攻撃力など）はMstEnemyStageParameterで定義されますが、今回は過去データを継承したため新規定義は不要です
