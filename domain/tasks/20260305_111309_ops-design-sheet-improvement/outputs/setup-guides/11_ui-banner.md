# UI・表示（ホームバナー等） マスタデータ設定手順書

## 概要

ホーム画面のバナー表示・漫画アニメーション・吹き出しテキストなど、UI 関連のマスタデータ設定手順書。

- **report.md 対応セクション**: `機能別データ詳細 > UI・表示`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstHomeBanner | ホーム画面バナー | 必須 |
| 2 | MstMangaAnimation | 漫画アニメーション（ステージ開始演出） | 条件付き必須 |
| 3 | MstSpeechBalloonI18n | インゲーム吹き出しテキスト | 必須 |

> **注意**: MstSpeechBalloonI18n は `02_unit.md`（ユニット追加）の一部として設定されることが多い。ユニット追加のタイミングで合わせて設定すること。

---

## 前提条件・依存関係

- **MstUnit の登録完了が前提**（MstSpeechBalloonI18n と MstMangaAnimation で参照）
- **MstEvent の登録完了が前提**（バナーの destination_path でイベント ID を使用）
- **MstStage の登録完了が前提**（MstMangaAnimation.mst_stage_id で参照）

---

## report.md から読み取る情報チェックリスト

- [ ] バナー数と表示期間
- [ ] 各バナーのリンク先（Event/Gacha）
- [ ] バナー表示優先度（sort_order）
- [ ] 漫画アニメーション有無（ストーリークエストのステージ開始演出）
- [ ] 吹き出しテキスト（新規ユニット追加時は `02_unit.md` と合わせて確認）

---

## テーブル別設定手順

### MstHomeBanner（ホーム画面バナー）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `32` |
| destination | バナークリック先種別（Event/Gacha/...） | `Event` |
| destination_path | リンク先 ID（Event なら event_id） | `event_you_00001` |
| asset_key | バナー画像アセットキー | `hometop_event_you_00001` |
| start_at | 表示開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 表示終了日時（UTC） | `2026-02-16 14:59:59` |
| sort_order | 表示優先度（数字が小さいほど上位） | `7` |
| release_key | 今回のリリースキー | `202602015` |

**destination 種別一覧**

| destination | 説明 | destination_path の形式 |
|------------|------|----------------------|
| Event | イベントページ | `event_{series}_{連番}` |
| Gacha | ガチャページ | `{gacha_id}`（OprGacha.id） |
| Quest | クエストページ | `quest_id` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, destination, destination_path, asset_key, start_at, end_at, sort_order, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstHomeBanner.csv')
ORDER BY sort_order;
```

**よくある設定パターン**
- 同一イベントでバナー2枚（イベントページ用・ガチャページ用）が標準
- 表示期間はイベント開始〜前半終了が多い（全期間ではない）

---

### MstMangaAnimation（漫画アニメーション）

ステージ開始時や敵出現時に漫画コマ演出を表示する設定。ストーリークエストステージで使用される。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `genga_{series_id}_{stage_kind}_{ステージ番号}_{連番2桁}_{trigger}` | `genga_you_event_1day_00001_01_start` |
| mst_stage_id | 対応するステージ ID | `event_you1_1day_00001` |
| condition_type | 表示トリガー（Start/EnemySummon/...） | `Start` |
| condition_value | トリガー値（EnemySummon の場合は何体目か） | `1` |
| animation_start_delay | 演出開始ディレイ（秒） | `0` |
| animation_speed | 再生速度（0.7=標準） | `0.7` |
| is_pause | 一時停止フラグ（1=一時停止あり） | `1` |
| can_skip | スキップ可能（1=可能） | `1` |
| asset_key | アニメーションアセットキー | `genga_you_event_1day_00001_01_start` |
| release_key | 今回のリリースキー | `202602015` |

**condition_type 種別一覧**

| condition_type | 説明 |
|---------------|------|
| Start | ステージ開始時 |
| EnemySummon | 敵出現時（condition_value = 何体目の敵か） |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_stage_id, condition_type, condition_value, animation_speed, asset_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMangaAnimation.csv')
ORDER BY mst_stage_id, condition_type;
```

---

### MstSpeechBalloonI18n（インゲーム吹き出しテキスト）

インゲームでユニットが話す吹き出しテキスト。新規ユニット追加時に登録（`02_unit.md` 参照）。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `222` |
| mst_unit_id | 対象ユニット ID | `chara_you_00001` |
| language | 言語コード | `ja` |
| condition_type | 表示タイミング（下表参照） | `SpecialAttackCharge` |
| balloon_type | 吹き出し形状（Maru/...） | `Maru` |
| side | 表示位置（Left/Right） | `Right` |
| duration | 表示時間（秒） | `0.5` |
| text | 吹き出しテキスト（`\n` で改行） | `私達はただ…\n\n子供を守る\n\nだけです` |
| release_key | 今回のリリースキー | `202602015` |

**condition_type 種別一覧**

| condition_type | 表示タイミング |
|---------------|-------------|
| SpecialAttackCharge | 必殺ワザチャージ時 |
| Summon | 召喚時 |
| LowHp | HP が低くなった時 |
| Victory | 勝利時 |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_unit_id, condition_type, balloon_type, side, duration, LEFT(text, 30) as text_preview
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstSpeechBalloonI18n.csv')
ORDER BY mst_unit_id, condition_type;
```

---

## 検証方法

- MstHomeBanner.destination_path:
  - Event の場合 → MstEvent.id が存在するか
  - Gacha の場合 → OprGacha.id が存在するか
- MstMangaAnimation.mst_stage_id → MstStage.id が存在するか
- MstSpeechBalloonI18n.mst_unit_id → MstUnit.id が存在するか
- MstHomeBanner の sort_order が重複していないか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
- 吹き出しテキスト（ユニット追加時）: `02_unit.md`
