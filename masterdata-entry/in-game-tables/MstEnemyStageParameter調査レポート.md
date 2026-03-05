# MstEnemyStageParameter 調査レポート

調査日: 2026-02-23
調査対象: MstEnemyStageParameter / MstEnemyCharacter / MstAutoPlayerSequence / MstInGame

---

## 1. エネミー設定の使いまわしパターン

### Q: インゲームごとに毎回エネミー設定を作っている？使いまわしている？

**答え: 「コンテンツ内での使いまわし」が主流。コンテンツをまたいだ使いまわしはほぼない。**

| 観点 | 結果 |
|------|------|
| 有効エネミーパラメータ総数 | 1,039件 |
| 複数インゲームで使いまわされているパラメータ | 364件（37%） |
| 1インゲームのみで使用 | 614件（63%） |

#### IDパターン別の使いまわし傾向

| IDパターン | パラメータ数 | 平均使用インゲーム数 | 最大使用インゲーム数 |
|----------|------------|-------------------|-------------------|
| mainquest系 | 33件 | **3.61** | 11 |
| イベント専用（charaget/challenge/savage等） | 252件 | 2.03 | 9 |
| general系（汎用） | 456件 | 1.88 | 20 |
| その他（ステージ固有） | 298件 | 1.41 | 9 |

**general系が最大20インゲームで使いまわされる**（`e_glo_00001_general_n_Normal_Colorless`は20ステージで使用）。

---

## 2. コンテンツをまたいだ使いまわし

### Q: インゲームコンテンツ横断の使いまわしはあるか？

**答え: ほぼなし（1.5%のみ）。各コンテンツでほぼ新規作成。**

| コンテンツ | ユニークエネミーパラメータ数 | ステージ数 |
|-----------|--------------------------|---------|
| mainquest | 462件 | 208 |
| raid | 145件 | 13 |
| sur1イベント | 43件 | 24 |
| hut1イベント | 38件 | 18 |
| jig1イベント | 37件 | 20 |
| yuw1イベント | 35件 | 26 |
| kim1イベント | 33件 | 18 |
| you1イベント | 30件 | 20 |
| osh1イベント | 29件 | 14 |
| dan1イベント | 28件 | 23 |
| spy1イベント | 26件 | 23 |
| f05イベント | 26件 | 15 |
| mag1イベント | 21件 | 23 |
| kai1イベント | 20件 | 23 |

#### 例外的なコンテンツ横断使いまわし（15件のみ）

ほとんどが「レイド ↔ 対応キャライベント」の組み合わせ。

| コンテンツ組み合わせ | 件数 |
|---|---|
| spy1 ↔ raid | 7件 |
| mag1 ↔ raid | 5件 |
| dan1 ↔ raid | 1件 |
| hut1 ↔ kim1 | 1件（別イベント間の例外） |
| mag1 ↔ dan1 | 1件（別イベント間の例外） |

---

## 3. 雑魚・ボスのロール設定パターン

### Q: 同一エネミーが雑魚にもボスにもなることはあるか？

**答え: ある。79%のエネミーがコンテンツによってNormal（雑魚）にもBoss（ボス）にもなる。**

### character_unit_kind の種類

| 値 | 説明 | 件数（MstEnemyStageParameter） |
|----|------|-------------------------------|
| `Normal` | 雑魚エネミー | 608件 |
| `Boss` | ボスエネミー | 423件 |
| `AdventBattleBoss` | 特殊ボス（kai系専用） | 8件 |

### 仕組み

- `mst_enemy_character_id`（キャラ種別）は同じでも、`MstEnemyStageParameter.id`はNormal用・Boss用で**別々に作成**
- HPや攻撃力もパラメータIDごとに個別定義（Bossは高HP/高攻撃力）
- `character_unit_kind`がNormal/Bossで分かれることでインゲーム側の挙動（ボスゲージ等）が切り替わる

---

## 4. MstEnemyCharacter（asset_key単位）ロールパターン一覧

### サマリー（136ユニークエネミー）

| ロールパターン | 件数 | 割合 |
|---|---|---|
| Normal + Boss（両方） | 107件 | 79% |
| Boss のみ | 21件 | 15% |
| Normal のみ | 14件 | 10% |
| Normal + Boss + AdventBattleBoss | 3件 | 2% |
| Normal + AdventBattleBoss | 1件 | 1% |
| 未使用（MstEnemyStageParameterなし） | 5件 | 4% |

> 詳細データ: [enemy_role_pattern.csv](./enemy_role_pattern.csv)

### パターン別の傾向

#### Normal + Boss（107件）= 主流パターン
大半のエネミーはコンテンツによってNormal（雑魚）にもBoss（ボス）にもなる。
`enemy_glo_00001`（最多使用キャラ）の例：
- mainquest: ほぼNormal（789回）、たまにBoss（4回）
- raid: Normal（96回）、Boss（5回）
- 各イベント: すべてNormal（雑魚）

#### Boss のみ（21件）= イベント専用ボス
`chara_mag_00201〜00401`、`chara_spy_00501`、`chara_osh_00501`など。
stage_param_countが1〜5件程度で用途が限定的。特定イベントのフィーチャードボス。

#### Normal のみ（14件）= 純粋な雑魚エネミー
`enemy_gom_00402〜00901`（gom系の量産雑魚）、`enemy_mag_00101/00301`など。
一度もボスとして使われていない雑魚専用キャラ。

#### Normal + AdventBattleBoss（1件）+ Normal + Boss + AdventBattleBoss（3件）= kai系限定
`enemy_kai_00001/00301/00401`（3種）と `enemy_kai_00101`（1種）のみ。
kai（カイ）固有のゲームシステム用の特殊区分。

#### 未使用（5件）
MstEnemyCharacterに定義はあるが、一度もMstEnemyStageParameterに使われていない：
- `chara_bat_00001` / `chara_bat_00101`（bat系）
- `enemy_spy_00201` / `enemy_spy_00301`（spy系）
- `chara_sur_00701`（sur系）

---

## 添付ファイル

| ファイル | 内容 |
|---------|------|
| [enemy_role_pattern.csv](./enemy_role_pattern.csv) | asset_key単位の全ロールパターン一覧（136件） |
| [enemy_ingame_usage_detail.csv](./enemy_ingame_usage_detail.csv) | エネミー × インゲーム使用詳細（コンテンツ別） |
