# BOXガシャのマスタデータ設定ガイド

## 目次
1. [BOXガシャとは？](#boxガシャとは)
2. [必要なテーブル一覧](#必要なテーブル一覧)
3. [テーブルの詳細説明](#テーブルの詳細説明)
4. [テーブル同士の関係性](#テーブル同士の関係性)
5. [設定の流れ（ステップバイステップ）](#設定の流れステップバイステップ)
6. [実例で理解する](#実例で理解する)
7. [よくある質問](#よくある質問)

---

## BOXガシャとは？

BOXガシャは、**中身が決まっている箱からアイテムを引くガチャ**です。通常のガチャと違い、「運が悪いとずっと目玉が出ない」ということがなく、**引き続ければ必ず全部のアイテムが手に入る**のが特徴です。

### プレイヤーの体験

1. **箱を開ける**: イベント通貨（専用アイテム）を使って、箱からアイテムを引く
2. **引いたアイテムは消える**: 一度引いたアイテムは箱から消えるので、重複しない
3. **箱が空になる or 手動でリセット**: 全部引き終わったら、または途中でも、次の箱に進める
4. **箱は複数段階ある**: BOX1 → BOX2 → BOX3 → 無限BOX というように進んでいく

### ゲームデザインのポイント

- **天井が見える**: 「あと〇回引けば必ず目玉が手に入る」と分かるので、プレイヤーは安心してイベントを周回できる
- **段階的な報酬**: 最初の箱は引きやすく、後半の箱ほど豪華な報酬にするなど、進行に合わせた設計が可能
- **無限BOX**: 全部の箱を引き終わった後も、無限に繰り返せる箱を用意できる

---

## 必要なテーブル一覧

BOXガシャを設定するには、**3つのマスタデータテーブル**が必要です。

| テーブル名 | 役割 | ゲーム体験での意味 |
|-----------|------|------------------|
| **MstBoxGacha** | BOXガシャ全体の設定 | 「このイベントのBOXガシャはどんなルールで動くか」を決める |
| **MstBoxGachaGroup** | 箱（BOX）の段階設定 | 「BOX1、BOX2、BOX3...と何段階の箱があるか」を決める |
| **MstBoxGachaPrize** | 箱の中身（景品）設定 | 「各箱に何が何個入っているか」を決める |

さらに、BOXガシャが関連するテーブルとして：

| 関連テーブル | 役割 |
|------------|------|
| **MstEvent** | イベント期間を管理（いつからいつまでBOXガシャが引けるか） |
| **MstItem** | 箱を引くためのコスト（イベント通貨）や、箱の中身のアイテムを定義 |
| **MstUnit** | 箱の中身にキャラクター（ユニット）が含まれる場合に使用 |
| **MstArtwork** | 箱の中身にアートワークが含まれる場合に使用 |

---

## テーブルの詳細説明

### 1. MstBoxGacha（BOXガシャ全体の設定）

**このテーブルで決めること:**
- どのイベントのBOXガシャか
- 箱を1回引くのに必要なアイテム（コスト）は何か
- 全ての箱を引き終わった後、どうするか（ループ設定）

**列の説明:**

| 列名 | 意味 | 具体例 |
|-----|------|--------|
| `id` | BOXガシャのID | `box_gacha_100kano_001` |
| `mst_event_id` | どのイベントに紐づくか | `event_100kano_202602` |
| `cost_id` | 1回引くのに必要なアイテムID | `item_event_coin_001`（イベント専用コイン） |
| `cost_num` | 1回引くのに必要なアイテム数 | `150`（コイン150個で1回） |
| `loop_type` | 全箱引き終わった後の動作 | `Last`（最後の箱を繰り返す） |

**`loop_type`の選択肢:**

| 値 | 意味 | 使い分け |
|----|------|----------|
| `Last` | 最後の箱（無限BOX）を繰り返す | **最も一般的**。全箱を引き終わった後も、同じ無限BOXを何度もリセットできる |
| `All` | 全ての箱（BOX1から）を繰り返す | 特殊なケース。全箱を引き終わったら、またBOX1から始まる |
| `First` | 最初の箱（BOX1）を繰り返す | ほぼ使わない。全箱を引き終わったら、BOX1だけを繰り返す |

**イメージ:**
```
MstBoxGacha = 「このBOXガシャのルールブック」
- イベント期間: 2026年2月1日〜2月28日
- 引くコスト: イベントコイン150個で1回
- ループ: 全箱引き終わったら最後の箱（無限BOX）を繰り返す
```

---

### 2. MstBoxGachaGroup（箱の段階設定）

**このテーブルで決めること:**
- BOX1、BOX2、BOX3...と、何段階の箱があるか
- 各箱のレベル（順番）

**列の説明:**

| 列名 | 意味 | 具体例 |
|-----|------|--------|
| `id` | 箱グループのID | `box_gacha_group_001` |
| `mst_box_gacha_id` | どのBOXガシャに紐づくか | `box_gacha_100kano_001` |
| `box_level` | 箱のレベル（段階） | `1`（BOX1）、`2`（BOX2）... |

**箱レベルの数え方:**
- `box_level = 1` → BOX1（最初の箱）
- `box_level = 2` → BOX2（2番目の箱）
- `box_level = 3` → BOX3（3番目の箱）
- `box_level = 4` → 無限BOX（最後の箱、繰り返し可能）

**イメージ:**
```
MstBoxGachaGroup = 「箱の段階表」
- BOX1: 初心者向け、引きやすい内容
- BOX2: 中級者向け、少し豪華
- BOX3: 上級者向け、かなり豪華
- 無限BOX: エンドレスで繰り返せる箱
```

**重要:** 1つのBOXガシャに対して、複数のMstBoxGachaGroupレコードを作成します。

---

### 3. MstBoxGachaPrize（箱の中身・景品設定）

**このテーブルで決めること:**
- 各箱に何が何個入っているか
- 目玉報酬はどれか

**列の説明:**

| 列名 | 意味 | 具体例 |
|-----|------|--------|
| `id` | 景品のID | `box_gacha_prize_001` |
| `mst_box_gacha_group_id` | どの箱に入っているか | `box_gacha_group_001`（BOX1に入っている） |
| `is_pickup` | 目玉報酬かどうか | `1`（目玉）、`0`（通常） |
| `resource_type` | 報酬の種類 | `Unit`（キャラクター）、`Item`（アイテム）、`Coin`（コイン）など |
| `resource_id` | 報酬の具体的なID | `unit_100kano_00001`（キャラクターのID） |
| `resource_amount` | 報酬の数量 | `1`（キャラクター1体）、`100`（コイン100枚）など |
| `stock` | 箱の中に何個入っているか | `1`（1個だけ）、`10`（10個入っている）など |

**`resource_type`の選択肢:**

| 値 | 意味 | 具体例 |
|----|------|--------|
| `Unit` | キャラクター | レアキャラクター、育成素材としてのキャラ等 |
| `Item` | アイテム | 強化素材、育成アイテム、イベントアイテム等 |
| `Artwork` | アートワーク | イラスト、背景画像等 |
| `Coin` | ゲーム内通貨（コイン） | 通常のコイン |
| `FreeDiamond` | 無償ダイヤ | 課金通貨の無償版 |

**`is_pickup`（目玉報酬）の使い方:**
- `1`: この景品が目玉報酬（ピックアップ対象）。画面で強調表示される
- `0`: 通常の景品

**`stock`（在庫数）の考え方:**
- `stock = 1`: 箱の中に1個だけ入っている（レアなキャラクターなど）
- `stock = 10`: 箱の中に10個入っている（よくあるアイテムなど）
- 箱の中身の合計個数は通常**100個**に設定します

**イメージ:**
```
BOX1の中身:
- 【目玉】レアキャラA（1個だけ） → is_pickup=1, stock=1
- 強化素材B（30個入り） → is_pickup=0, stock=30
- コイン1000枚（20個入り） → is_pickup=0, stock=20
- アイテムC（49個入り） → is_pickup=0, stock=49
合計: 100個
```

---

## テーブル同士の関係性

### 関係図（親子関係）

```
MstEvent（イベント）
    ↓ 紐づく
MstBoxGacha（BOXガシャ全体のルール）
    ↓ 持つ
MstBoxGachaGroup（箱の段階：BOX1、BOX2、BOX3...）
    ↓ 含む
MstBoxGachaPrize（箱の中身：景品）
```

### 1対多の関係

```
1つのMstBoxGacha
    ↓
複数のMstBoxGachaGroup（BOX1、BOX2、BOX3、無限BOX）
    ↓
各MstBoxGachaGroupに対して複数のMstBoxGachaPrize（景品）
```

### 具体的な数のイメージ

```
MstBoxGacha: 1件
    ├─ MstBoxGachaGroup (BOX1): 1件
    │    ├─ MstBoxGachaPrize (キャラA): 1件 (stock=1)
    │    ├─ MstBoxGachaPrize (アイテムB): 1件 (stock=30)
    │    ├─ MstBoxGachaPrize (コイン): 1件 (stock=20)
    │    └─ MstBoxGachaPrize (その他): 1件 (stock=49)
    │         → 合計4件の景品設定、中身は100個
    ├─ MstBoxGachaGroup (BOX2): 1件
    │    └─ MstBoxGachaPrize: 複数件（中身合計100個）
    ├─ MstBoxGachaGroup (BOX3): 1件
    │    └─ MstBoxGachaPrize: 複数件（中身合計100個）
    └─ MstBoxGachaGroup (無限BOX): 1件
         └─ MstBoxGachaPrize: 複数件（中身合計100個）
```

---

## 設定の流れ（ステップバイステップ）

### ステップ1: イベントを作成（MstEvent）

まず、BOXガシャを開催するイベント自体を設定します。

**設定例:**
- イベントID: `event_100kano_202602`
- 開催期間: 2026年2月1日 00:00 〜 2026年2月28日 23:59

**ポイント:** BOXガシャはイベント期間内でのみ引けます。

---

### ステップ2: コストアイテムを用意（MstItem）

BOXガシャを引くために必要なアイテム（イベント通貨）を用意します。

**設定例:**
- アイテムID: `item_event_coin_100kano`
- アイテム名: 「100カノコイン」
- 用途: イベントクエストをクリアすると獲得できる

**ポイント:** プレイヤーはイベントを遊んでこのコインを集め、BOXガシャを引きます。

---

### ステップ3: 報酬アイテム・キャラを用意（MstItem / MstUnit）

箱の中に入れる景品（キャラクター、アイテム等）を事前に定義します。

**設定例:**
- キャラクター: `unit_100kano_00001` 〜 `unit_100kano_00003`（3体の新キャラ）
- 強化アイテム: `item_enhance_stone_01`（強化石）
- 育成アイテム: `item_training_book_01`（育成本）

**ポイント:** これらは既存のマスタデータで定義されているものを使います。

---

### ステップ4: BOXガシャ全体を設定（MstBoxGacha）

BOXガシャ全体のルールを決めます。

**CSVの例:**
```csv
id,mst_event_id,cost_id,cost_num,loop_type
box_gacha_100kano_001,event_100kano_202602,item_event_coin_100kano,150,Last
```

**意味:**
- BOXガシャID: `box_gacha_100kano_001`
- イベント: `event_100kano_202602`に紐づく
- コスト: `item_event_coin_100kano`（100カノコイン）を150個使って1回引ける
- ループ: 全箱を引き終わったら、最後の箱（無限BOX）を繰り返す

---

### ステップ5: 箱の段階を設定（MstBoxGachaGroup）

BOX1、BOX2、BOX3、無限BOXという段階を作ります。

**CSVの例:**
```csv
id,mst_box_gacha_id,box_level
box_gacha_group_100kano_lv1,box_gacha_100kano_001,1
box_gacha_group_100kano_lv2,box_gacha_100kano_001,2
box_gacha_group_100kano_lv3,box_gacha_100kano_001,3
box_gacha_group_100kano_infinite,box_gacha_100kano_001,4
```

**意味:**
- BOX1（レベル1）: `box_gacha_group_100kano_lv1`
- BOX2（レベル2）: `box_gacha_group_100kano_lv2`
- BOX3（レベル3）: `box_gacha_group_100kano_lv3`
- 無限BOX（レベル4）: `box_gacha_group_100kano_infinite`

**ポイント:** `box_level`は1から順番に番号を振ります。

---

### ステップ6: 各箱の中身を設定（MstBoxGachaPrize）

各箱に何を何個入れるかを決めます。**箱1個あたりの中身の合計は100個**にします。

**BOX1の例（初心者向け、引きやすい内容）:**
```csv
id,mst_box_gacha_group_id,is_pickup,resource_type,resource_id,resource_amount,stock
prize_100kano_lv1_001,box_gacha_group_100kano_lv1,1,Unit,unit_100kano_00001,1,1
prize_100kano_lv1_002,box_gacha_group_100kano_lv1,0,Item,item_enhance_stone_01,10,30
prize_100kano_lv1_003,box_gacha_group_100kano_lv1,0,Coin,null,1000,20
prize_100kano_lv1_004,box_gacha_group_100kano_lv1,0,Item,item_training_book_01,5,49
```

**意味:**
- 【目玉】キャラクター1体（`unit_100kano_00001`）が1個だけ入っている
- 強化石10個セットが30個入っている
- コイン1000枚が20個入っている
- 育成本5個セットが49個入っている
- 合計: 1 + 30 + 20 + 49 = **100個**

**BOX2、BOX3、無限BOX**も同様に設定します。段階が進むほど豪華な報酬にすると良いでしょう。

---

## 実例で理解する

### 実例: 「100カノイベント」のBOXガシャ

**シナリオ:**
- イベント名: 「君のことが大大大大大好きな100人の彼女 いいジャン祭」
- 期間: 2026年2月1日〜2月28日
- BOXガシャの構成:
  - BOX1〜BOX3: 通常の箱（1回ずつ引き切り）
  - 無限BOX: 何度でもリセットして引ける

### データ設定の全体像

#### 1. MstEvent
```csv
id,start_at,end_at
event_100kano_202602,2026-02-01 00:00:00,2026-02-28 23:59:59
```

#### 2. MstItem（コスト）
```csv
id,item_type,name
item_event_coin_100kano,EventCurrency,100カノコイン
```

#### 3. MstBoxGacha
```csv
id,mst_event_id,cost_id,cost_num,loop_type
box_gacha_100kano_001,event_100kano_202602,item_event_coin_100kano,150,Last
```

#### 4. MstBoxGachaGroup（4段階の箱）
```csv
id,mst_box_gacha_id,box_level
box_gacha_group_100kano_lv1,box_gacha_100kano_001,1
box_gacha_group_100kano_lv2,box_gacha_100kano_001,2
box_gacha_group_100kano_lv3,box_gacha_100kano_001,3
box_gacha_group_100kano_infinite,box_gacha_100kano_001,4
```

#### 5. MstBoxGachaPrize（BOX1の中身のみ例示）
```csv
id,mst_box_gacha_group_id,is_pickup,resource_type,resource_id,resource_amount,stock
prize_100kano_lv1_001,box_gacha_group_100kano_lv1,1,Unit,unit_100kano_00001,1,1
prize_100kano_lv1_002,box_gacha_group_100kano_lv1,0,Item,item_enhance_stone_rare,10,15
prize_100kano_lv1_003,box_gacha_group_100kano_lv1,0,Item,item_enhance_stone_normal,10,35
prize_100kano_lv1_004,box_gacha_group_100kano_lv1,0,Coin,null,1000,30
prize_100kano_lv1_005,box_gacha_group_100kano_lv1,0,Item,item_training_book_01,3,19
```
**合計:** 1 + 15 + 35 + 30 + 19 = **100個**

**BOX2、BOX3、無限BOX**も同様に、それぞれ100個の中身を設定します。

---

### プレイヤーの体験の流れ

1. **イベント開始**: プレイヤーはイベントクエストをクリアして「100カノコイン」を集める
2. **BOX1を引く**: コイン150個で1回引ける。目玉キャラを狙って引き続ける
3. **BOX1が空 or 手動リセット**: BOX1を引き切るか、途中でリセットしてBOX2へ進む
4. **BOX2、BOX3と進む**: 段階的に豪華な報酬を獲得していく
5. **無限BOXに到達**: BOX3まで引き終わったら、無限BOXを何度でもリセットして引ける
6. **イベント終了まで周回**: イベント終了までコインを集めてBOXガシャを引き続ける

---

## よくある質問

### Q1: 箱の中身は必ず100個にしないといけないのですか？

**A:** はい、**1つの箱あたり100個**が推奨です。これはプレイヤーの体験設計上、100個程度が適切とされているためです。MstBoxGachaPrizeの`stock`を合計して100個になるように設定してください。

---

### Q2: 目玉報酬（`is_pickup`）は1つの箱に複数設定できますか？

**A:** はい、できます。例えば、レアキャラ2体を目玉にする場合、2つのMstBoxGachaPrizeレコードで`is_pickup=1`に設定します。

---

### Q3: 無限BOXは本当に無限に引けるのですか？

**A:** はい、**イベント期間内であれば何度でもリセットして引けます**。無限BOXに到達した後は、プレイヤーが手動でリセットするたびに、同じ内容の箱が復活します。

---

### Q4: BOX1〜BOX3の中身を全て同じにできますか？

**A:** 技術的には可能ですが、ゲーム体験としては**段階的に豪華にする**ことを推奨します。例えば：
- BOX1: 初心者向け、普通の報酬
- BOX2: 中級者向け、レアな報酬が増える
- BOX3: 上級者向け、最高レアの報酬
- 無限BOX: BOX3と同等か、やや控えめな内容

---

### Q5: 途中でBOXをリセットした場合、残りのアイテムはどうなりますか？

**A:** 現在の仕様では、**残りのアイテムは破棄される**（次のBOXは初期状態から始まる）設計になっています。プレイヤーは目玉を引いた後、残りを引かずに次のBOXに進むことができます。

---

### Q6: `loop_type`は`Last`以外を使うことはありますか？

**A:** ほとんどのケースで`Last`（最後の箱を繰り返す）を使います。
- `All`（全箱をループ）: 特殊なイベントで、BOX1〜BOX3を何周もさせたい場合に使用
- `First`（最初の箱をループ）: ほぼ使いません

**推奨: 迷ったら`Last`を選んでください。**

---

### Q7: コストは必ずイベント専用アイテムでないといけませんか？

**A:** いいえ、`cost_id`には任意のアイテムIDを設定できます。ただし、一般的には：
- **イベント専用通貨**（イベントクエストで獲得）を使うのが最も一般的
- 通常のコインやダイヤを使うことも技術的には可能ですが、ゲーム体験として推奨されません

---

### Q8: 1回引きと10回引きで割引を設定できますか？

**A:** はい、可能です。`cost_num`は1回引きのコストを設定しますが、10回引きの場合は別途設定で割引を適用できます。例えば：
- 1回引き: 150個
- 10回引き: 1,400個（通常1,500個のところ100個割引）

ただし、これは別のテーブル（OprGachaUseResource等）で設定するため、ここでは`cost_num`に1回あたりのコストのみを設定してください。

---

### Q9: キャラクター（Unit）が重複した場合はどうなりますか？

**A:** 既存のガチャと同じく、**自動でキャラクターのかけら（アイテム）に変換**されます。BOXガシャ固有の設定は不要で、既存の変換ルールがそのまま適用されます。

---

### Q10: 箱の段階は4段階（BOX1〜3 + 無限BOX）でないといけませんか？

**A:** いいえ、自由に設定できます。例えば：
- シンプルなイベント: BOX1 + 無限BOX（2段階のみ）
- 長期イベント: BOX1〜5 + 無限BOX（6段階）

**ポイント:** `box_level`を1から順番に設定し、最後のレベルを無限BOXにします。

---

## まとめ

BOXガシャのマスタデータ設定は、以下の流れで行います：

1. **イベントを作る**（MstEvent）
2. **コストアイテムを用意**（MstItem）
3. **BOXガシャ全体のルールを決める**（MstBoxGacha）
4. **箱の段階を作る**（MstBoxGachaGroup）
5. **各箱の中身を設定する**（MstBoxGachaPrize）

テーブルの関係性は以下の通り：
```
MstEvent
  ↓
MstBoxGacha（1件）
  ↓
MstBoxGachaGroup（BOX1、BOX2、BOX3、無限BOX など複数件）
  ↓
MstBoxGachaPrize（各箱ごとに景品を複数件、合計100個）
```

このガイドを参考に、プレイヤーが楽しめるBOXガシャを設定してください！

---

**困ったときは:**
- 既存のBOXガシャ設定（`projects/glow-masterdata/MstBoxGacha*.csv`）を参照する
- サーバーAPI要件書（`projects/glow-server/docs/sdd/features/BOXガシャ/`）を確認する
- 不明点があればエンジニアに相談する
