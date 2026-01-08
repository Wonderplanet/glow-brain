# BOXガシャ テーブル関係図

## 全体像

```mermaid
graph TB
    Event[MstEvent<br/>イベント<br/>━━━━━━━━━━━━━<br/>id: event_100kano_202602<br/>期間: 2026-02-01 ~ 2026-02-28]
    
    BoxGacha[MstBoxGacha<br/>BOXガシャ設定<br/>━━━━━━━━━━━━━<br/>id: box_gacha_100kano_001<br/>コスト: item_event_coin_100kano を 150個<br/>ループ: Last 最後の箱を繰り返す]
    
    Group1[MstBoxGachaGroup<br/>BOX1<br/>━━━━━━━━━━━━━<br/>box_level: 1]
    Group2[MstBoxGachaGroup<br/>BOX2<br/>━━━━━━━━━━━━━<br/>box_level: 2]
    Group3[MstBoxGachaGroup<br/>BOX3<br/>━━━━━━━━━━━━━<br/>box_level: 3]
    
    Prize1A[MstBoxGachaPrize<br/>BOX1の中身<br/>━━━━━━━━━━━━━<br/>★目玉 キャラA<br/>stock: 1個]
    Prize1B[強化石<br/>stock: 30個]
    Prize1C[コイン<br/>stock: 20個]
    Prize1D[その他<br/>stock: 49個]
    
    Prize2A[MstBoxGachaPrize<br/>BOX2の中身<br/>━━━━━━━━━━━━━<br/>★目玉 キャラB<br/>stock: 1個]
    Prize2B[レア強化石<br/>stock: 40個]
    Prize2C[コイン<br/>stock: 30個]
    Prize2D[その他<br/>stock: 29個]
    
    Event -->|mst_event_id で紐づく| BoxGacha
    BoxGacha -->|mst_box_gacha_id で紐づく| Group1
    BoxGacha -->|mst_box_gacha_id で紐づく| Group2
    BoxGacha -.->|同様に| Group3
    
    Group1 -->|mst_box_gacha_group_id で紐づく| Prize1A
    Group1 --> Prize1B
    Group1 --> Prize1C
    Group1 --> Prize1D
    
    Group2 -->|mst_box_gacha_group_id で紐づく| Prize2A
    Group2 --> Prize2B
    Group2 --> Prize2C
    Group2 --> Prize2D
    
    style Event fill:#e1f5ff
    style BoxGacha fill:#fff4e6
    style Group1 fill:#f3e5f5
    style Group2 fill:#f3e5f5
    style Group3 fill:#f3e5f5
    style Prize1A fill:#ffebee
    style Prize2A fill:#ffebee
```

---

## データの流れ（プレイヤー視点）

```mermaid
sequenceDiagram
    participant P as プレイヤー
    participant Q as イベントクエスト
    participant G as BOXガシャ画面
    participant S as サーバー
    
    P->>Q: クエストをクリア
    Q->>P: イベント通貨（100カノコイン）獲得
    P->>G: BOXガシャ画面を開く
    
    Note over G: 現在の箱: BOX1<br/>残り: 100個<br/>コスト: 100カノコイン 150個で1回<br/><br/>【目玉】<br/>★★★ キャラA （残り1個）<br/><br/>【その他】<br/>強化石 （残り30個）<br/>コイン1000枚 （残り20個）<br/>育成本 （残り49個）
    
    P->>G: 「1回引く」または「10回引く」を選択
    G->>S: 抽選実行リクエスト
    S->>S: BOX1の残りアイテムからランダムに取得
    
    Note over S: 【結果】<br/>✓ 強化石 10個
    
    S->>G: 抽選結果を返す
    G->>P: 結果を表示
    
    Note over G: 現在の箱: BOX1<br/>残り: 99個<br/><br/>【目玉】<br/>★★★ キャラA （残り1個）<br/><br/>【その他】<br/>強化石 （残り29個）← 引いたので減った<br/>コイン1000枚 （残り20個）<br/>育成本 （残り49個）
    
    P->>P: 目玉を引くまで or<br/>箱が空になるまで繰り返す
    P->>G: 箱をリセット
    G->>S: リセット実行
    S->>G: BOX2へ進む
```

---

## テーブル間のデータ参照の流れ

### パターン1: BOXガシャを引くとき

```mermaid
sequenceDiagram
    autonumber
    participant C as クライアント
    participant API as サーバーAPI
    participant MST as マスタDB
    participant USR as ユーザーDB
    
    C->>API: box_gacha_100kano_001 を 1回引く
    API->>MST: MstBoxGacha取得
    Note over MST: コスト: item_event_coin_100kano が 150個
    
    API->>USR: プレイヤーのアイテム所持数チェック
    Note over USR: 100カノコインを150個以上持っているか？
    
    API->>USR: プレイヤーの現在のBOX番号を取得<br/>UsrBoxGacha
    Note over USR: 現在 box_level = 1（BOX1）
    
    API->>MST: MstBoxGachaGroup取得
    Note over MST: box_level = 1 のグループID:<br/>box_gacha_group_100kano_lv1
    
    API->>MST: MstBoxGachaPrize取得
    Note over MST: box_gacha_group_100kano_lv1<br/>に含まれる景品一覧
    
    API->>USR: プレイヤーの残りアイテムを取得<br/>UsrBoxGachaStock
    Note over USR: BOX1残り:<br/>キャラA 1個、強化石 29個<br/>コイン 20個、育成本 49個
    
    API->>API: ランダム抽選
    Note over API: 残りアイテムから1個選ぶ<br/>→ コイン1000枚
    
    API->>USR: アイテム配布 & 残りアイテム削除
    Note over USR: ・プレイヤーにコイン1000枚付与<br/>・BOX1の残りコイン: 20個→19個
    
    API->>C: レスポンス
    Note over C: 獲得: コイン1000枚<br/>BOX1残り: 98個
```

### パターン2: BOXをリセットするとき

```mermaid
sequenceDiagram
    autonumber
    participant C as クライアント
    participant API as サーバーAPI
    participant MST as マスタDB
    participant USR as ユーザーDB
    
    C->>API: box_gacha_100kano_001 をリセット
    
    API->>USR: プレイヤーの現在のBOX番号を取得<br/>UsrBoxGacha
    Note over USR: 現在 box_level = 1（BOX1）
    
    API->>API: 次のBOX番号を計算
    Note over API: box_level = 1 → 次は box_level = 2（BOX2）
    
    API->>MST: MstBoxGachaGroup取得
    Note over MST: box_level = 2 のグループID:<br/>box_gacha_group_100kano_lv2
    
    API->>MST: MstBoxGachaPrize取得
    Note over MST: box_gacha_group_100kano_lv2<br/>に含まれる景品一覧
    
    API->>USR: プレイヤーの残りアイテムを初期化<br/>UsrBoxGachaStock
    Note over USR: BOX2の中身をマスタからコピーして<br/>残りアイテムとして設定
    
    API->>USR: プレイヤーのBOX番号を更新<br/>UsrBoxGacha
    Note over USR: box_level = 2 に更新
    
    API->>C: レスポンス
    Note over C: BOX2に進みました<br/>残り: 100個
```

---

## マスタデータとユーザーデータの関係

```mermaid
graph TB
    subgraph Master["マスタデータ（変更されない）"]
        M1[MstBoxGacha<br/>BOXガシャのルール]
        M2[MstBoxGachaGroup<br/>箱の段階定義]
        M3[MstBoxGachaPrize<br/>箱の初期状態・中身]
    end
    
    subgraph User["ユーザーデータ（プレイごとに変化）"]
        U1[UsrBoxGacha<br/>プレイヤーの進捗<br/>現在のBOX番号、引いた回数等]
        U2[UsrBoxGachaStock<br/>プレイヤーのBOX残りアイテム<br/>引くたびに減る]
    end
    
    Master -->|BOX初回アクセス時<br/>コピー| User
    
    style Master fill:#e3f2fd
    style User fill:#fff3e0
```

**ポイント:**
- マスタデータは「設計図」
- ユーザーデータは「プレイヤーごとの状態」
- BOX初回アクセス時、マスタの中身をコピーしてユーザーデータを作成
- プレイヤーがガシャを引くたびに、ユーザーデータの残りアイテムが減る

---

## 具体例: プレイヤーAさんの状態

### マスタデータ（全プレイヤー共通）
```
MstBoxGachaPrize（BOX1の初期状態）
┌──────────────────────────────────────┐
│ キャラA:  1個                        │
│ 強化石:  30個                        │
│ コイン:  20個                        │
│ 育成本:  49個                        │
│ ────────────────────────────────     │
│ 合計:   100個                        │
└──────────────────────────────────────┘
```

### プレイヤーAさんのデータ（Aさん専用）
```
UsrBoxGacha（Aさんの進捗）
┌──────────────────────────────────────┐
│ 現在のBOX: BOX1（box_level = 1）    │
│ 累計引き回数: 25回                  │
└──────────────────────────────────────┘

UsrBoxGachaStock（AさんのBOX1残りアイテム）
┌──────────────────────────────────────┐
│ キャラA:  1個 ← まだ引いてない     │
│ 強化石:  18個 ← 12個引いた（30→18）│
│ コイン:  13個 ← 7個引いた（20→13） │
│ 育成本:  43個 ← 6個引いた（49→43） │
│ ────────────────────────────────     │
│ 合計:   75個 ← 25個引いた（100→75）│
└──────────────────────────────────────┘
```

**Aさんの体験:**
- 25回引いて、まだキャラA（目玉）が出ていない
- 残り75個なので、「あと75回引けば必ずキャラAが手に入る」と分かる
- これが「天井の可視化」の仕組み

---

## ループタイプ別の動作

### loop_type = "Last"（最も一般的）

```mermaid
graph LR
    B1[BOX1<br/>100個] --> B2[BOX2<br/>100個]
    B2 --> B3[BOX3<br/>100個]
    B3 --> INF[無限BOX<br/>100個]
    INF -->|何度でもリセット| INF
    
    style INF fill:#ffe0b2
```

### loop_type = "All"（特殊ケース）

```mermaid
graph LR
    B1[BOX1<br/>100個] --> B2[BOX2<br/>100個]
    B2 --> B3[BOX3<br/>100個]
    B3 --> INF[無限BOX<br/>100個]
    INF -->|BOX1に戻る| B1
    
    style INF fill:#fff9c4
```

### loop_type = "First"（ほぼ使わない）

```mermaid
graph LR
    B1[BOX1<br/>100個] --> B2[BOX2<br/>100個]
    B2 --> B3[BOX3<br/>100個]
    B3 -->|BOX1に戻る| B1
    B1 -->|BOX1だけを繰り返し| B1
    
    style B1 fill:#ffccbc
```

---

## まとめ

BOXガシャは以下の階層構造で管理されます：

```mermaid
graph TB
    Event[イベント<br/>MstEvent]
    BoxGacha[BOXガシャ全体<br/>MstBoxGacha]
    
    Box1[BOX1<br/>MstBoxGachaGroup]
    Box2[BOX2<br/>MstBoxGachaGroup]
    Box3[BOX3<br/>MstBoxGachaGroup]
    BoxInf[無限BOX<br/>MstBoxGachaGroup]
    
    Prize1A[景品1<br/>MstBoxGachaPrize]
    Prize1B[景品2<br/>MstBoxGachaPrize]
    Prize1C[...<br/>合計100個]
    
    Prize2[景品...<br/>合計100個]
    Prize3[景品...<br/>合計100個]
    PrizeInf[景品...<br/>合計100個]
    
    Event --> BoxGacha
    BoxGacha --> Box1
    BoxGacha --> Box2
    BoxGacha --> Box3
    BoxGacha --> BoxInf
    
    Box1 --> Prize1A
    Box1 --> Prize1B
    Box1 --> Prize1C
    
    Box2 --> Prize2
    Box3 --> Prize3
    BoxInf --> PrizeInf
    
    style Event fill:#e1f5ff
    style BoxGacha fill:#fff4e6
    style Box1 fill:#f3e5f5
    style Box2 fill:#f3e5f5
    style Box3 fill:#f3e5f5
    style BoxInf fill:#ffe0b2
```

各テーブルの役割：
- **MstBoxGacha**: ルールブック（コスト、ループ設定）
- **MstBoxGachaGroup**: 箱の段階表（BOX1、BOX2、BOX3、無限BOX）
- **MstBoxGachaPrize**: 箱の中身リスト（何が何個入っているか）

このシンプルな3テーブル構造で、プレイヤーに「天井が見える安心感」を提供できます！
