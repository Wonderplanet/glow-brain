# Step 01: 敵キャラ基礎ステータス設計

VDインゲーム設計書（design.md）の **`### 敵キャラ設計`** セクション（敵キャラ選定テーブル＋敵キャラステータステーブル）を生成・更新する手順。

- **担当セクション**: `## レベルデザイン > ### 敵キャラ設計 > #### 敵キャラ選定` と `#### 敵キャラステータス`
- **キャラ選定は行わない**: 引数で渡されたキャラIDを前提とする

---

## Step 0: 準備・ドキュメント読み込み

以下を確認・読み込む。

**テーブル詳細ドキュメント（必須）**:
- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md`

**既存EnemyStageParameter参照CSV（必須）**:
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv
```

**メインクエスト実績参照（キャラが存在すれば）**:
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/メインクエスト_Normal難易度_エネミー/` 配下のMDファイルを確認
  - ファイル命名: `{MstEnemyCharacter.id}_{MstEnemyCharacterI18n.name}.md`
  - 対象キャラIDのファイルが存在すれば Read tool で読み込み、実績ステータスを参考にする

## Step 1: ステータス設計

引数のキャラIDごとに以下のカラムを決定する。

| カラム | 説明 | 設計方針 |
|--------|------|---------|
| `kind` | キャラ種別 | `e_` → `Normal`、`c_` → `Counter`、`boss` → `Boss` |
| `role_type` | 役割 | `Attack` / `Defense` / `Balanced` |
| `color` | 色属性 | IDから読み取る（Yellow/Red/Green/Blue/Colorless） |
| `base_hp` | 基礎HP | メインクエスト実績値を参考に、Normal難易度相当で設定 |
| `base_atk` | 基礎攻撃力 | 同上 |
| `base_spd` | 移動速度 | 同上 |
| `well_dist` | 攻撃範囲 | 通常は `1.5`〜`3.0` |
| `knockback` | ノックバック | 通常は `0` または `1` |
| `combo` | コンボ数 | 通常は `2`〜`5` |
| `drop_bp` | ドロップBP | 通常は `10`〜`50` |

**ステータス設計の基準値（Normal難易度相当）**:
- 雑魚（e_キャラ）: `base_hp` = 15,000〜50,000、`base_atk` = 200〜800
- c_キャラ: `base_hp` = 30,000〜80,000（雑魚より強め）
- ボス: `base_hp` = 100,000〜500,000、`base_atk` = 500〜2,000

## Step 2: 設計テーブル生成

以下のMarkdownテーブルを生成する。

```markdown
#### 敵キャラ選定（MstEnemyCharacter）
| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| {id} | {名前} | {ボス/雑魚/フレンド} | {備考} |

#### 敵キャラステータス（MstEnemyStageParameter）
> 既存参照の場合はその旨と参照先バッチを注記
| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| {id} | {名前} | {値} | {値} | {値} | {値} | {値} | {値} | {値} | {値} | {値} | {値} |
```

**既存IDの扱い**:
- `vd_all/data/MstEnemyStageParameter.csv` に既に存在するIDは「既存参照」と注記し、CSVの既存値を転記する
- 新規IDはステータスを設計して記載する

## Step 3: 確認・更新

`--batch` フラグがない場合:
```
敵キャラ基礎ステータスを設計しました。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

承認後（または `--batch` 時）、design.md の該当セクションを更新する。

---

## ガードレール

1. **キャラ選定は引数のみ**: 引数に含まれないキャラIDを独自に追加してはいけない
2. **既存IDのステータスは上書きしない**: 既存CSV値をそのまま使用する
3. **bossブロックはボスのみ**: bossブロックでは雑魚キャラを追加しない
4. **normalブロックは15体以上設計できる体数を確保**: 敵キャラの合計出現数が15体以上になれる構成を選ぶ

---

## リファレンス

- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` — テーブル定義
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv` — 既存敵パラメータ一覧
- `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/specs/メインクエスト_Normal難易度_エネミー/` — 実績ステータス参照
