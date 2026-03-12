# MstInGame 設定パターン詳細ドキュメント 作成プロンプト

## 目的

`MstInGame` の全カラムについて、設定方法・依存関係・パターンを詳細にまとめたドキュメントを作成する。

---

## Step 1: masterdata-explorer でスキーマ・実データを調査

以下のプロンプトでスキルを呼び出す。

```
/masterdata-explorer MstInGame データの設定方法について詳しくまとめたい。
この列をこの設定にしたら、こういう設定をする必要があるといった設定パターンを漏れなく正確にまとめてほしい。
各列ごとに、正確に詳細に、どんな設定をすれば良いのかを全て把握したい。

まとめたら保存先：specs/*.md
```

### スキルが調査する内容

- DBスキーマ（`mst_in_games` テーブル）・CSVテンプレート
- content_type / stage_type の enum 値（VD固有値: `Dungeon` / `vd_normal` / `vd_boss`）
- bgm_asset_key / loop_background_asset_key の実際の値パターン
- 既存CSVデータのサンプル（VDブロックの実データ）

---

## Step 2: C#実装で確認すべきポイント

スキーマや既存ドキュメントの説明が実装と一致しているか確認する。
特に `mst_auto_player_sequence_id` と `mst_auto_player_sequence_set_id` の使い分けや、
content_type / stage_type の参照箇所は、クライアント実装を確認しないと正確にわからない。

### 調査対象ファイル

| ファイル | 確認内容 |
|---------|---------|
| MstInGame をロードするファクトリ or リポジトリ | content_type / stage_type の参照箇所 |
| InGame 関連の Domain クラス | mst_auto_player_sequence_id と mst_auto_player_sequence_set_id の使い分け |

### 確認のポイント

**`mst_auto_player_sequence_id` と `mst_auto_player_sequence_set_id` の違いは何か？**

- 両方必要か、片方だけでよいか
- sequence_set_id は MstAutoPlayerSequence.sequence_set_id と対応する（= MstInGame.id と一致させる）

**content_type / stage_type は実装でどう参照されているか？**

- VD固定値: `content_type=Dungeon` / `stage_type=vd_normal` または `vd_boss`
- 既存ドキュメント（`domain/knowledge/masterdata/table-docs/MstInGame.md`）に記載がない場合、CLAUDE.md の記述と実装の一致を確認する

---

## Step 3: 設定パターン詳細ドキュメントの内容

`specs/MstInGame_設定パターン詳細.md` に以下の内容をまとめる。

1. **概要**: MstInGame の役割とテーブル間リレーション構造
2. **全カラム一覧**: 型・NULL・デフォルト・VD固定値
3. **他テーブルとのリレーション構造**: mst_page_id / mst_enemy_outpost_id / boss_mst_enemy_stage_parameter_id / mst_auto_player_sequence_set_id の各参照先
4. **content_type / stage_type の enum 一覧**: VD固有値（Dungeon / vd_normal / vd_boss）を含む全パターン
5. **BGM・背景アセットの設定パターン**: bgm_asset_key / loop_background_asset_key の実際の値例
6. **ステータス倍率（*_coef）の設定ガイド**: 各 coef カラムの意味と VD での設定例
7. **VD固有の設定値一覧**: Normal / Boss ブロックの典型構成
8. **IDの命名規則**: `{block_id}` パターン
9. **注意事項まとめ**: よくある落とし穴（sequence_set_id と id の一致など）

---

## 関連ドキュメント

- `domain/knowledge/masterdata/table-docs/MstInGame.md` — テーブル基本解説
- `specs/MstInGame_設定パターン詳細.md` — 本プロンプトの成果物
