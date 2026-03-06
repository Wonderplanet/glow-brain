---
name: masterdata-ingame-detail-explainer
description: GLOWインゲームコンテンツの詳細解説ドキュメント作成。CSVからMstAutoPlayerSequenceのグループ構造・MstEnemyStageParameterの敵パラメータを読み取り、Mermaidフロー図付きMarkdownを生成します。「インゲーム詳細解説」「バトル解説」「敵出現解説」「シーケンス解説」「グループ構造解説」などのキーワードで使用します。
---

# インゲーム詳細解説ドキュメント作成スキル

GLOWインゲームコンテンツのCSVを解析し、Mermaidフロー図付きの詳細解説Markdownを生成します。

## 保存先

```
domain/knowledge/masterdata/in-game/guides/{INGAME_ID}.md
```

## 5ステップ作業フロー

### Step 1: MstInGame 基本情報取得
```bash
duckdb -c "SELECT * FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE id = '{INGAME_ID}';"
```

### Step 2: 関連テーブルを一括クエリ（5テーブル + I18n必須）
1. `MstEnemyOutpost` — WHERE id = '{INGAME_ID}'
2. `MstInGameI18n` — WHERE mst_in_game_id = '{INGAME_ID}' AND language = 'ja'
3. `MstPage` + `MstKomaLine` (JOIN) — WHERE p.id = '{INGAME_ID}'
4. `MstAutoPlayerSequence` — WHERE sequence_set_id = '{INGAME_ID}' ORDER BY sequence_group_id, sequence_element_id
5. `MstEnemyStageParameter` — Step4のSummonEnemyのaction_valueからIN句でフィルタ

**Step 2-6: 敵キャラI18n日本語名取得（必須）**
- `MstEnemyStageParameter.mst_enemy_character_id` → `MstEnemyCharacterI18n.name` のマッピングを作成
- `{キャラID: 日本語名}` の辞書を構築し、以降のドキュメント生成で必ず使用する
- セクション3の日本語名カラム・Mermaidノードラベルに日本語名を適用する（IDのまま記載しない）

**Step 2-7: アビリティI18n日本語説明取得（アビリティがある場合は必須）**
- `MstEnemyStageParameter.mst_unit_ability_id1` → `MstAbilityI18n.description` のマッピングを作成
- セクション3のabilityカラムにアビリティIDではなく日本語説明を記載する

詳細クエリは [duckdb-queries.md](references/duckdb-queries.md) を参照。

### Step 3: グループ構造解析
- `sequence_group_id` が NULL/空 → デフォルトグループ
- `sequence_element_id` が `groupchange_N` → グループ切り替えトリガー行
- `action_type = 'SwitchSequenceGroup'` の `action_value` → 遷移先グループID
- condition_type マッピング: `FriendUnitDead(N)` / `ElapsedTimeSinceSequenceGroupActivated(N)` / `OutpostHpPercentage(N)`

### Step 4: Mermaidフローチャート生成
グループ切り替え情報から **`flowchart TD`（上下方向・推奨）** を構築。
- `flowchart LR`（横方向）はノードラベルが長いと見切れるため使用しない
- ノードラベルには必ず日本語名（Step 2-6で取得）を使用する（キャラIDのまま記載しない）
- ノードラベルは簡潔に「グループID + 主な敵の日本語名（種別）」に絞り、詳細はセクション5に委ねる

スタイル: デフォルト=`#6b7280` / w1〜w2=`#3b82f6` / w3〜w4=`#f59e0b` / w5以降=`#ef4444` / ループ起点直前=`#8b5cf6`

**Mermaidノード記述ルール（必須）**:
- 改行は `<br/>` を使用する（`\n` はレンダラーによって文字として表示される）
- 絵文字はノードラベルに含めない（レンダリングが崩れる場合がある）
- ひし形ノード `{}` は使用しない → 長方形 `[]` で代替する（`{}` 内では `<br/>` が動作しないため）
- `style` には必ず `color:#fff` を明示する（背景色によっては文字が見えなくなる）
- ノードラベルは3行以内に収める（見切れ防止）

### Step 5: ドキュメント組み立て・保存（8セクション必須）
1. 概要（箇条書き形式）
2. 関連テーブル設定（MstInGame / MstEnemyOutpost / MstPage+MstKomaLine / MstInGameI18n）
3. 使用する敵パラメータ一覧（カラム解説 + 全パラメータ表 + 特性解説）
4. グループ構造の全体フロー（Mermaid）
5. 全N行の詳細データ（グループ単位）
6. グループ切り替えまとめ表
7. スコア体系
8. この設定から読み取れる設計パターン（3〜6項目）

詳細テンプレートは [document-structure.md](references/document-structure.md) を参照。

## コンテンツタイプ別の主な違い

| 項目 | event（砦破壊型） | raid（スコアアタック型） |
|------|-----------------|------------------------|
| `is_damage_invalidation` | 空（ダメージ有効） | `1`（砦HP実質無限） |
| ゲーム目的 | 砦を破壊してクリア | 時間内にスコアを稼ぐ |
| ループ構造 | w4→w1 タイマーループ | wN→w1 撃破数ループ |
| w5並行グループ | 各グループから並行起動 | なし |
| InitialSummon | 通常なし | デフォルトグループで使用 |

詳細は [content-type-guide.md](references/content-type-guide.md) を参照。

## 参照ドキュメント

- [document-structure.md](references/document-structure.md) — 8セクション詳細テンプレート
- [content-type-guide.md](references/content-type-guide.md) — コンテンツタイプ別の差異
- [duckdb-queries.md](references/duckdb-queries.md) — 使用するDuckDBクエリ集
- [event-example.md](examples/event-example.md) — eventタイプの例示（event_jig1_savage_00001）
- [raid-example.md](examples/raid-example.md) — raidタイプの例示（raid_osh1_00001）
