# 原画 要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstArtwork.csv`（原画定義・1イベントにつき通常2行）
> - `MstArtworkI18n.csv`（原画名・説明文・通常2行）
> - `MstArtworkFragment.csv`（ピース定義・1原画につき16行 × 2枚 = 通常32行）
> - `MstArtworkFragmentI18n.csv`（ピース名・通常32行）
> - `MstArtworkFragmentPosition.csv`（ピース位置・通常32行）

---

## テンプレート

```
# 原画 要件テキスト

## 基本情報

- イベントID: {mst_events.id に設定したIDをそのまま記入}
  例: event_kim_00001
- シリーズ略称（3文字）: {mst_series.id として登録する英字略称。新規シリーズのみ}
  例: kim
- リリースキー: {このリリースのリリースキーを記入}
  例: 202602020

## 原画A（1枚目）

- 原画名: {MstArtworkI18n.name に設定する表示名。最大40文字}
- 説明文: {MstArtworkI18n.description に設定する説明文。最大255文字。改行したい場合は \n と明記}
- アセットキー末尾番号: 0001
  （自動生成ルール: {シリーズ略称}_0001 → asset_key: event_{略称}_0001）

## 原画B（2枚目）

- 原画名: {MstArtworkI18n.name に設定する表示名。最大40文字}
- 説明文: {MstArtworkI18n.description に設定する説明文。最大255文字}
- アセットキー末尾番号: 0002

## ピース配置（ドロップグループとステージの対応）

原画A のピース割り当て（各ステージでどのピースグループが落ちるか）:

{ステージID}: ピース {N}枚（グループ名: {drop_group_id}）

---
【記入欄】
ステージ1:
ステージ2:
ステージ3:
ステージ4:
（合計16ピースになるよう、各ステージに割り振ってください）

原画B のピース割り当て:

ステージ1:
ステージ2:
ステージ3:
ステージ4:

  ※ ピース合計は必ず16枚（4×4グリッド）
  ※ ステージ数と1ステージあたりのピース枚数は自由（慣例: 4ステージ×4枚、または8ステージ×2枚）
  ※ 具体的なステージIDが未定の場合は「ステージ数: {N}、1ステージあたりのピース数: {M}」と書けばOK
```

---

## ピース配置パターンの選択肢

ステージ数と1ステージあたりのピース枚数は以下の慣例から選んでください。

| パターン | ステージ数 | 1ステージあたりピース数 | 採用例 |
|---------|----------|---------------------|-------|
| 4×4パターン（標準・最新） | 4ステージ | 4枚 | event_kim_00001, event_hut_00001（202602020〜） |
| 6×2〜3パターン | 6ステージ | 2〜3枚（合計16） | event_jig_00001（202601010） |
| 8×2パターン | 8ステージ | 2枚 | event_mag_00001（202511010） |

**新規イベントでは「4×4パターン」を使うことを推奨します。**

---

## ピース位置（position）の自動設定について

ピース位置（1〜16のグリッド番号）は **Claudeが自動でランダム散在配置** します。
プランナーによる個別指定は不要です。

```
グリッドイメージ（4×4）:
 1  2  3  4
 5  6  7  8
 9 10 11 12
13 14 15 16
```

自動配置では1〜16がすべて使用され、各グリッドに重複なく割り当てられます。

---

## 記入済みサンプル（実データ: event_kim_00001 より）

```
# 原画 要件テキスト

## 基本情報

- イベントID: event_kim_00001
- シリーズ略称（3文字）: kim
- リリースキー: 202602020

## 原画A（1枚目）

- 原画名: キスゾンビ
- 説明文: 彼女たちがキスを求めるキスゾンビに！愛の力でキスバイオハザードを生き残れ！

## 原画B（2枚目）

- 原画名: 運命の出会い
- 説明文: 一目見た瞬間、全身に走った衝撃。この二人が愛城 恋太郎の"運命の人"…!?

## ピース配置

ステージ数: 4、1ステージあたりのピース数: 4
（原画A・B それぞれ同じ4×4パターン）

ステージIDの命名規則:
  原画A: event_kim1_charaget01_00001 〜 00004（グループ: event_kim_a_0001 〜 0004）
  原画B: event_kim1_charaget02_00001 〜 00006（グループ: event_kim_b_0001 〜 0006）
  ※ ステージID自体はMstStageで別途設定。ここではdrop_group_idの命名のみ決める。
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、原画マスタデータCSVを生成してください。

【生成対象】
- MstArtwork.csv（新規2行：原画A・B）
- MstArtworkI18n.csv（新規2行）
- MstArtworkFragment.csv（新規32行：各原画16ピース×2）
- MstArtworkFragmentI18n.csv（新規32行）
- MstArtworkFragmentPosition.csv（新規32行）

【固定値】
- rarity: SSR（全原画・全ピース共通）
- outpost_additional_hp: 100（全原画共通）
- drop_percentage: 100（全ピース共通）
- ENABLE: e（全行）

【ピース位置】
1〜16をランダム散在で自動配置してください（原画A・Bそれぞれ重複なし）。

【ID採番ルール】
- MstArtwork.id: artwork_event_{略称}_0001, artwork_event_{略称}_0002
- MstArtworkFragment.id: artwork_fragment_event_{略称}_00001〜00016（原画A）、
                          artwork_fragment_event_{略称}_00101〜00116（原画B）
- MstArtworkFragmentPosition.id: fragment.id と同じ値
- drop_group_id: event_{略称}_a_{0001〜000N}（原画A）、event_{略称}_b_{0001〜000N}（原画B）

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係

```
MstSeries（1行）               ← 新規シリーズの場合のみ別途設定
  └─ id: {略称}（例: kim）

MstArtwork（2行: 原画A・B）
  ├─ id: artwork_event_{略称}_0001 / _0002
  ├─ mst_series_id: {略称}
  ├─ outpost_additional_hp: 100（固定）
  ├─ asset_key: event_{略称}_0001 / _0002
  ├─ sort_order: 01 / 02
  ├─ rarity: SSR（固定）
  └─ release_key: {リリースキー}
    ↓
MstArtworkI18n（2行）
  ├─ id: artwork_event_{略称}_0001_ja / _0002_ja
  ├─ mst_artwork_id: artwork_event_{略称}_0001 / _0002
  ├─ language: ja
  ├─ name: {原画名}
  └─ description: {説明文}
    ↓
MstArtworkFragment（16行 × 2枚 = 32行）
  ├─ id: artwork_fragment_event_{略称}_00001〜00016（原画A）
  │       artwork_fragment_event_{略称}_00101〜00116（原画B）
  ├─ mst_artwork_id: artwork_event_{略称}_0001（原画A）/ _0002（原画B）
  ├─ drop_group_id: event_{略称}_a_0001〜000N（原画A）
  │                 event_{略称}_b_0001〜000N（原画B）
  │   ← MstStage.mst_artwork_fragment_drop_group_id と一致させる
  ├─ drop_percentage: 100（固定）
  ├─ rarity: SSR（固定）
  └─ asset_num: 1〜16（各ピースに1〜16を1つずつ割り当て）
    ↓
MstArtworkFragmentI18n（32行）
  ├─ id: {fragment_id}_ja
  ├─ mst_artwork_fragment_id: {fragment_id}
  ├─ language: ja
  └─ name: 原画のかけら{asset_num}（例: 原画のかけら7）

MstArtworkFragmentPosition（32行）
  ├─ id: {fragment_id}（fragment.id と同値）
  ├─ mst_artwork_fragment_id: {fragment_id}
  └─ position: 1〜16（原画Aの16ピース・原画Bの16ピースをそれぞれ重複なく割り当て）

MstStage（別CSV・参照のみ）
  └─ mst_artwork_fragment_drop_group_id: drop_group_id と紐づく
     ← 各ステージがどのピースグループを落とすかをここで設定
```

---

## 注意事項

- **rarity カラム**: 202602020 以降のリリースから追加。新規作成時は必ず `SSR` を設定する。
- **outpost_additional_hp**: 全原画共通で `100`。レアリティに関係なく固定。
- **asset_num の意味**: ピースのデザイン番号（1〜16）であり、`position`（グリッド配置番号）とは別の概念。ただし実データでは asset_num = position となる場合が多い。
- **drop_group_id の命名**: `event_{略称}_a_{連番4桁}` の形式。原画Aは `a`、原画Bは `b` でグループを分ける。ステージ数が変わればグループ数も変わる。
- **MstStage との紐づき**: ピースがどのステージで落ちるかは `MstStage.mst_artwork_fragment_drop_group_id` で設定する。本CSV生成の対象外（MstStage 設定時に参照する）。
- **ピース位置の一意性**: 同一原画の16ピース内で `position` が重複してはいけない。1〜16をそれぞれ1回ずつ使用する。
- **MstSeries の事前確認**: `mst_series_id` に指定するシリーズが `MstSeries` テーブルに存在するか確認すること。新規シリーズの場合は `MstSeries.csv` への追加も必要。
- **時刻はすべてJST前提**（UTC変換不要）。
