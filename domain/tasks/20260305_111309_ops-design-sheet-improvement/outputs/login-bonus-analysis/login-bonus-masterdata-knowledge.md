# ログインボーナス マスタデータ設定ナレッジ

> 作成日: 2026-03-06
> 対象: 全リリースキー（202511010〜202603025）のログインボーナスデータを横断分析

---

## 1. 関連テーブル構造

ログインボーナスは**3つのテーブル**で管理される。

```
MstMissionEventDailyBonusSchedule  ← いつ開催するか
        ↓ mst_event_id で紐付け
MstMissionEventDailyBonus          ← 何日目に何を渡すか（1行=1日分）
        ↓ mst_mission_reward_group_id で紐付け
MstMissionReward                   ← 実際の報酬内容（アイテム種別・数量）
```

### MstMissionEventDailyBonusSchedule

テーブルコメント: 「イベントデイリーボーナス(イベントログボ)のスケジュール設定」

| カラム | 型 | NULL | デフォルト | 説明 |
|-------|-----|------|-----------|------|
| id | varchar(255) | NOT NULL | ─ | スケジュールID（`{event_id}_daily_bonus`） |
| mst_event_id | varchar(255) | NOT NULL | ─ | 対象イベントID（`mst_events.id`） |
| start_at | timestamp | NOT NULL | ─ | 開始日時 |
| end_at | timestamp | NOT NULL | ─ | 終了日時 |
| release_key | bigint | NOT NULL | 1 | リリースキー |

インデックス: `PRIMARY (id)`, `index_mst_event_id (mst_event_id)`

### MstMissionEventDailyBonus

テーブルコメント: 「イベントデイリーボーナス(イベントログボ)の設定」

| カラム | 型 | NULL | デフォルト | 説明 |
|-------|-----|------|-----------|------|
| id | varchar(255) | NOT NULL | ─ | ボーナスID（`{event_id}_daily_bonus_{n}`） |
| mst_mission_event_daily_bonus_schedule_id | varchar(255) | NOT NULL | ─ | スケジュールIDへの外部キー（`mst_mission_event_daily_bonus_schedules.id`） |
| login_day_count | int unsigned | NOT NULL | ─ | 条件とするログイン日数（1始まり） |
| mst_mission_reward_group_id | varchar(255) | NOT NULL | ─ | 報酬グループID（`mst_mission_reward_groups.id`）。MstMissionRewardの`group_id`と紐付く |
| sort_order | int unsigned | NOT NULL | 0 | 表示順 |
| release_key | bigint | NOT NULL | 1 | リリースキー |

インデックス: `PRIMARY (id)`, `uk_schedule_id_login_day_count (mst_mission_event_daily_bonus_schedule_id, login_day_count)` ※ユニーク制約

### MstMissionReward（ログインボーナス部分）

テーブルコメント: 「ミッション報酬設定」

| カラム | 型 | NULL | デフォルト | 説明 |
|-------|-----|------|-----------|------|
| id | varchar(255) | NOT NULL | ─ | 報酬ID（`mission_reward_{連番}`） |
| release_key | bigint | NOT NULL | 1 | リリースキー |
| group_id | varchar(255) | NOT NULL | ─ | 報酬グルーピングID。`MstMissionEventDailyBonus.mst_mission_reward_group_id`と一致 |
| resource_type | enum | NOT NULL | ─ | 報酬タイプ（`Exp` / `Coin` / `FreeDiamond` / `Item` / `Emblem` / `Unit` / `ArtworkFragment`） |
| resource_id | varchar(255) | NULL | ─ | 報酬リソースID（resource_typeがItemなど固有IDを持つ場合のみ） |
| resource_amount | int unsigned | NOT NULL | 0 | 報酬の個数 |
| sort_order | int unsigned | NOT NULL | ─ | 並び順 |

インデックス: `PRIMARY (id)`

---

## 2. ID命名規則

```
スケジュールID:   {event_id}_daily_bonus
ボーナスID:       {event_id}_daily_bonus_{ゼロパディング2桁}
報酬グループID:   {event_id}_daily_bonus_{ゼロパディング2桁}  ← ボーナスIDと同一
```

> **注意**: 初期（202511010〜202512010）は2桁ゼロパディングなし（`_1`, `_2`...）。
> 202512020以降は2桁ゼロパディング（`_01`, `_02`...）に統一。

---

## 3. リリースキー別データ一覧

イベントのシリーズIDは `MstSeries.id` で管理されており、作品名は `MstSeriesI18n.name` に定義されている。

| リリースキー | 作品名（MstSeriesI18n.name） | シリーズID（MstSeries.id） | イベント名 | 期間 | ログイン日数 | スケジュールID |
|------------|---------------------------|--------------------------|----------|------|------------|-------------|
| 202511010 | 株式会社マジルミエ | `mag` | マジルミエ いいジャン祭 | 2025-11-06〜11-25 | 19日 | event_mag_00001_daily_bonus |
| 202511020 | 2.5次元の誘惑 | `yuw` | 2.5次元の誘惑 いいジャン祭 | 2025-11-25〜12-08 | 13日 | event_yuw_00001_daily_bonus |
| 202512010 | 魔都精兵のスレイブ | `sur` | 魔都精兵のスレイブ いいジャン祭 | 2025-12-08〜2026-01-01 | 24日 | event_sur_00001_daily_bonus |
| 202512020 | 【推しの子】 | `osh` | 推しの子 いいジャン祭 | 2026-01-01〜01-16 | 15日 | event_osh_00001_daily_bonus |
| 202601010 | 地獄楽 | `jig` | 地獄楽 いいジャン祭 | 2026-01-16〜02-02 | 17日 | event_jig_00001_daily_bonus |
| 202602015 | 幼稚園WARS | `you` | 幼稚園WARS いいジャン祭 | 2026-02-02〜02-16 | 14日 | event_you_00001_daily_bonus |
| 202602020 | 君のことが大大大大大好きな100人の彼女 | `kim` | 100カノ いいジャン祭 | 2026-02-16〜03-02 | 14日 | event_kim_00001_daily_bonus |
| 202603010 | ふつうの軽音部 | `hut` | ふつうの軽音部 いいジャン祭 | 2026-03-02〜03-16 | 14日 | event_hut_00001_daily_bonus |
| 202603025 | ─（シリーズID非該当） | `glo`（GLOW本体プレフィックス） | ハーフアニバーサリー感謝祭 後半 | 2026-03-30〜04-13 | 15日 | event_glo_00003_daily_bonus |

> **注意**:
> - シリーズIDはイベントIDのプレフィックスとして使われる（例: `event_mag_00001` の `mag` が `MstSeries.id`）
> - `glo` はシリーズIDではなくGLOWゲーム本体のプレフィックス。MstSeriesには存在しない
> - 202512015（ログインボーナスなし）、202602010（ログインボーナスなし）は対象外

---

## 4. 報酬アイテムIDマスタ

| アイテムID | 表示名 | 備考 |
|-----------|-------|------|
| `ticket_glo_00003` | ピックアップガシャチケット | 最も価値の高いガシャ券 |
| `ticket_glo_00002` | スペシャルガシャチケット | 2番目に価値の高いガシャ券 |
| `ticket_osh_10000` | 推しの子SSR確定ガシャ | コラボ限定（202512020のみ） |
| `memory_glo_00002` | カラーメモリー・レッド | 強化素材（属性:赤） |
| `memory_glo_00003` | カラーメモリー・ブルー | 強化素材（属性:青） |
| `memory_glo_00004` | カラーメモリー・イエロー | 強化素材（属性:黄） |
| `memory_glo_00005` | カラーメモリー・グリーン | 強化素材（属性:緑） |
| `memoryfragment_glo_00001` | メモリーフラグメント・初級 | 育成素材（下位） |
| `memoryfragment_glo_00002` | メモリーフラグメント・中級 | 育成素材（中位） |
| `memoryfragment_glo_00003` | メモリーフラグメント・上級 | 育成素材（上位）※稀 |
| `stamina_item_glo_00001` | スタミナアイテム | AP回復 |
| `item_glo_00001` | いいジャンメダル・赤 | 交換所用メダル（202512020のみ） |
| ─（reward_type: `Coin`） | コイン | 5,000〜10,000 |
| ─（reward_type: `FreeDiamond`） | プリズム | 20〜150 |

---

## 5. 報酬構成パターン分析

### 5-1. 標準パターン（いいジャン祭型）

202601010以降で安定したパターン：**14日周期を基準に設計**。

| 日目 | 報酬カテゴリ | 標準内容 | 備考 |
|-----|------------|---------|------|
| 1日目 | ガシャチケット（大） | ピックアップガシャチケット×2 | 初日の目玉報酬 |
| 2日目 | コイン | 5,000〜10,000 | |
| 3日目 | プリズム | 40〜50 | |
| 4日目 | メモリーフラグメント（初級） | 5〜15個 | |
| 5日目 | メモリーフラグメント（中級）またはカラーメモリー | 4個 / 300〜600 | |
| 6日目 | カラーメモリー | 300〜600 | イベントキャラの属性色 |
| 7日目 | ガシャチケット（次位） | スペシャルガシャチケット×2 | 7日目の特別報酬 |
| 8日目 | コイン | 5,000 | |
| 9日目 | プリズム | 40〜50 | |
| 10日目 | メモリーフラグメント（初級） | 10個 | |
| 11日目 | カラーメモリー | 300〜600 | 別の属性色 |
| 12日目 | コイン | 5,000 | |
| 13日目 | ピックアップガシャチケット×1 | ラスト2日への引き | |
| 14日目 | スペシャルガシャチケット×1 | 最終日のまとめ報酬 | |

> **設計原則**: 7日目・14日目にガシャチケットを配置し、継続ログインのモチベーション維持を図る。

### 5-2. 報酬量の推移トレンド

| 時期 | ピックアップチケット(1日目) | プリズム(1回) | カラーメモリー(1回) |
|------|--------------------------|-------------|-----------------|
| 初期（202511010〜202512010） | ×1 | 20〜30 | 100〜300 |
| 中期（202512020〜202601010） | ×2（202601010から） | 40〜50 | 300 |
| 安定期（202602015〜202603010） | ×2 | 50 | 600 |

> **トレンド**: 時期が進むにつれ、報酬量が全体的に増加（インフレ傾向）。

---

## 6. 特殊パターン

### 6-1. 推しの子コラボ（202512020）

**通常と異なる1日目報酬**: `ticket_osh_10000`（推しの子SSR確定ガシャ）という**コラボ限定の超高価値チケット**を1日目に配置。

また、`item_glo_00001`（いいジャンメダル・赤）という**コラボ専用アイテム**が登場。このアイテムは交換所での素材に使用される施策連動型の報酬。

```
1日目: ticket_osh_10000 × 1  ← コラボ限定SSR確定ガシャ（特別）
3日目: item_glo_00001 × 200  ← いいジャンメダル・赤（コラボ専用）
```

### 6-2. ハーフアニバーサリー後半（202603025）

**通常イベントではなく周年記念施策**のため、構成が全く異なる。

- 全15日間、報酬が**プリズム（FreeDiamond）のみ**
- 前半5日: 100プリズム/日（計500）
- 後半10日: 50プリズム/日（計500）
- **合計1,000プリズム**（記念らしい丸い数字に設計）

```
日1〜5:  FreeDiamond × 100
日6〜15: FreeDiamond × 50
合計:    FreeDiamond × 1,000
```

### 6-3. 事前登録ログインボーナス（202603010 event_hut_00002）

同一リリースキーに**2つ目のログインボーナス**（event_hut_00002）が含まれているが、備考欄に「仮ログインボーナス(今は非開催)」との記載あり。`MstMissionEventDailyBonusSchedule`にもスケジュールが存在しない。

> **示唆**: 先行してデータを仕込んでおき、後のリリースキーでスケジュールを追加して開催する運用パターンと思われる。

---

## 7. 日数設計パターン

| 日数 | 理由/法則 |
|------|----------|
| **14日** | 最も多い標準パターン（202602015〜202603010は全て14日）。2週間周期のイベント設計に対応 |
| **15日** | 202512020（`osh`/【推しの子】）、202603025（ハーフアニバ）。特別感を出すための+1日 |
| **17日** | 202601010（`jig`/地獄楽）。イベント期間が17日のためそのまま |
| **19日** | 202511010（`mag`/株式会社マジルミエ）。初期のイベントで不規則 |
| **13日** | 202511020（`yuw`/2.5次元の誘惑）。短期イベント |
| **24日** | 202512010（`sur`/魔都精兵のスレイブ）。年末年始を跨ぐ長期イベント |

---

## 8. マスタデータ作成手順（標準14日パターン）

### Step 1: MstMissionEventDailyBonusSchedule を作成

```csv
ENABLE,id,release_key,mst_event_id,start_at,end_at
e,{event_id}_daily_bonus,{YYYYMMXXX},{event_id},"{開始日時}","{終了日時}"
```

> - `start_at` / `end_at` は`YYYY-MM-DD HH:MM:SS`形式
> - イベント期間と一致させる
> - 通常は`15:00:00`開始、`03:59:59`終了（翌4:00 AM前終了）

### Step 2: MstMissionEventDailyBonus を14日分作成

```csv
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day_count,mst_mission_reward_group_id,sort_order,備考
e,{event_id}_daily_bonus_01,{YYYYMMXXX},{event_id}_daily_bonus,1,{event_id}_daily_bonus_01,1,ピックアップガシャチケット
e,{event_id}_daily_bonus_02,{YYYYMMXXX},{event_id}_daily_bonus,2,{event_id}_daily_bonus_02,1,コイン
...（14日分）
```

> - `login_day_count`は1〜14の連番
> - `mst_mission_reward_group_id`は`id`と同一値
> - `sort_order`は常に1

### Step 3: MstMissionReward にログインボーナス分を追加

各日分を1行ずつ追加：

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_{連番},{YYYYMMXXX},{event_id}_daily_bonus_01,Item,ticket_glo_00003,2,1,"{イベント名} 特別ログインボーナス"
e,mission_reward_{連番},{YYYYMMXXX},{event_id}_daily_bonus_02,Coin,,5000,1,"{イベント名} 特別ログインボーナス"
e,mission_reward_{連番},{YYYYMMXXX},{event_id}_daily_bonus_03,FreeDiamond,,50,1,"{イベント名} 特別ログインボーナス"
...
```

> - `group_id`: `MstMissionEventDailyBonus.mst_mission_reward_group_id` と一致させる
> - `resource_type`: `Exp` / `Coin` / `FreeDiamond` / `Item` / `Emblem` / `Unit` / `ArtworkFragment` のいずれか
> - `Coin`・`FreeDiamond`の場合、`resource_id`は空（カラム自体は必要）
> - `sort_order`: 1日につき1報酬のため通常は`1`固定
> - 備考欄は`"{イベント名} 特別ログインボーナス"`で統一

---

## 9. 標準14日パターン テンプレート

イベントキャラクターの属性色に応じてカラーメモリーのアイテムIDを選択：

| 属性 | memory item ID |
|------|---------------|
| 赤 | `memory_glo_00002` |
| 青 | `memory_glo_00003` |
| 黄 | `memory_glo_00004` |
| 緑 | `memory_glo_00005` |

```
1日目:  ピックアップガシャチケット × 2   (ticket_glo_00003, quantity:2)
2日目:  コイン × 5,000                  (Coin, quantity:5000)
3日目:  プリズム × 50                   (FreeDiamond, quantity:50)
4日目:  メモリーフラグメント初級 × 5     (memoryfragment_glo_00001, quantity:5)
5日目:  メモリーフラグメント中級 × 4     (memoryfragment_glo_00002, quantity:4)
6日目:  カラーメモリー[属性A] × 600      (memory_glo_000XX, quantity:600)
7日目:  スペシャルガシャチケット × 2     (ticket_glo_00002, quantity:2)
8日目:  コイン × 5,000                  (Coin, quantity:5000)
9日目:  プリズム × 50                   (FreeDiamond, quantity:50)
10日目: メモリーフラグメント初級 × 10    (memoryfragment_glo_00001, quantity:10)
11日目: カラーメモリー[属性B] × 600      (memory_glo_000XX, quantity:600)
12日目: コイン × 5,000                  (Coin, quantity:5000)
13日目: ピックアップガシャチケット × 1   (ticket_glo_00003, quantity:1)
14日目: スペシャルガシャチケット × 1     (ticket_glo_00002, quantity:1)
```

---

## 10. よくあるミスと注意点

1. **IDのゼロパディング**: 202512020以降は`_01`〜（2桁）。古いデータは`_1`〜（1桁）。新規作成は**2桁**に統一すること。

2. **スケジュールの終了時刻**: イベント終了は`03:59:59`（翌4:00 AM前）が慣例。ただし初期イベント（202511010〜）は`10:59:59`だったため変更された経緯あり。**現行は03:59:59を使用**。

3. **MstMissionRewardの備考**: `"{イベント名} 特別ログインボーナス"`のフォーマットを守ること。イベント名は日本語表記。

4. **報酬グループIDの一致**: `MstMissionEventDailyBonus.mst_mission_reward_group_id`と`MstMissionReward.mst_mission_reward_group_id`は**完全一致**が必要。

5. **reward_idの空欄**: `Coin`・`FreeDiamond`タイプの場合、CSVでは`reward_id`カラムを**空文字（カンマだけ）**にする（NULLではなく空文字）。

6. **スケジュールのmst_event_id**: `MstMissionEventDailyBonusSchedule.mst_event_id`はMstEvent.idと一致している必要がある。

---

## 11. 特別施策時の設計指針

| 施策タイプ | 推奨設計 |
|----------|---------|
| **コラボイベント** | 標準14日パターン + 1日目にピックアップチケット×2 |
| **コラボ特別版** | 1日目にコラボ限定チケット（ticket_{コラボ名}_{番号}）を配置 |
| **周年・アニバーサリー** | プリズムのみで統一し、合計が記念数値（1,000等）になるよう設計 |
| **短期イベント（〜14日未満）** | 14日パターンを短縮（7日目特別報酬は維持する） |
| **長期イベント（14日超）** | 14日パターン＋追加日（後半は低価値報酬のループ）|
