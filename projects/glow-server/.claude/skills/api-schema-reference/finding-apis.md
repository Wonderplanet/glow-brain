# APIの探し方とファイル構成

glow-schemaリポジトリでAPIの仕様を見つける方法を説明します。

## ディレクトリ構造

```
glow-schema/
├── Schema/              # ← YAMLファイルの格納場所
│   ├── User.yml        # ユーザー関連API
│   ├── Stage.yml       # ステージ関連API
│   ├── Gacha.yml       # ガチャ関連API
│   ├── Party.yml       # パーティ関連API
│   ├── Shop.yml        # ショップ関連API
│   ├── Mission.yml     # ミッション関連API
│   └── ...             # その他29個のYAMLファイル
├── SchemaBuilder/      # スキーマビルダー（自動生成ツール）
└── README.md
```

## glow-schemaリポジトリの場所

プロジェクトルートから見た相対パス:
```
../glow-schema/
```

**確認方法:**
```bash
ls ../glow-schema/Schema/
```

## APIを探す手順

### 1. エンドポイントパスから探す

**例:** `/api/stage/start` の仕様を確認したい

#### ステップ1: ファイルを推測
エンドポイントパスの第1セグメント（`/api/` の次）がファイル名のヒントになります。

```
/api/stage/start → Stage.yml
/api/user/info → User.yml
/api/gacha/draw → Gacha.yml
/api/party/set → Party.yml
```

#### ステップ2: YAMLファイルを開く
```bash
cat ../glow-schema/Schema/Stage.yml
```

#### ステップ3: `api` セクションを探す
YAMLファイルの末尾付近に `api:` セクションがあります。

```yaml
api:
  - name: Stage
    actions:
      - name: Start
        path: "/api/stage/start"  # ← ここで確認
        ...
```

### 2. 機能名から探す

**例:** ガチャ機能のAPI一覧を見たい

#### ステップ1: 機能名に対応するYAMLファイルを開く
```bash
cat ../glow-schema/Schema/Gacha.yml
```

#### ステップ2: `api` セクションのすべての `actions` を確認
```yaml
api:
  - name: Gacha
    actions:
      - name: Prize
        path: "/api/gacha/prize"
        method: GET
      - name: Ad
        path: "/api/gacha/draw/ad"
        method: POST
      - name: Diamond
        path: "/api/gacha/draw/diamond"
        method: POST
```

### 3. 全YAMLファイルをgrepで検索

**例:** `/api/user/change_name` の定義を探す

```bash
grep -r "change_name" ../glow-schema/Schema/
```

**出力例:**
```
../glow-schema/Schema/User.yml:      - name: ChangeName
../glow-schema/Schema/User.yml:        path: "/api/user/change_name"
```

## 主要なYAMLファイル一覧

| ファイル名 | 内容 |
|-----------|------|
| User.yml | ユーザー情報、プロフィール、ログイン、スタミナ購入 |
| Stage.yml | ステージ開始・終了、クエスト、ステージ報酬 |
| Gacha.yml | ガチャ引き、ガチャ確率、ガチャ履歴 |
| Party.yml | パーティ編成、ユニット設定 |
| Shop.yml | ショップ、アイテム購入 |
| Mission.yml | ミッション、デイリーミッション |
| Unit.yml | ユニット、ユニット強化、進化 |
| Item.yml | アイテム、アイテム使用 |
| Idle.yml | 放置報酬、オフライン報酬 |
| Pvp.yml | PvP対戦、ランキング |
| AdventBattle.yml | アドベントバトル |
| Encyclopedia.yml | 図鑑 |
| Outpost.yml | 拠点 |
| Game.yml | ゲーム全体の設定、初回起動 |
| Error.yml | エラーコード定義 |
| System.yml | システム設定、メンテナンス |
| Opr.yml | 運営データ |
| Mst.yml | 共通マスターデータ |

## API定義の読み方

### 基本情報

```yaml
api:
  - name: Stage  # API名（Controller名に対応することが多い）
    actions:
      - name: Start  # アクション名（メソッド名に対応）
        path: "/api/stage/start"  # ← エンドポイントパス
        params:  # ← リクエストパラメータ
          - name: mstStageId
            type: string
          - name: partyNo
            type: int
          - name: isChallengeAd
            type: bool
        method: POST  # ← HTTPメソッド
        response: StageStartResultData  # ← レスポンス型
```

### リクエストパラメータ

**params リスト:**
- `name`: パラメータ名（Controller で `$request->input('name')` でアクセス）
- `type`: データ型（[type-system.md](type-system.md) 参照）

**パラメータなしの例:**
```yaml
- name: Info
  path: "/api/user/info"
  params:  # 空リスト
  method: GET
  response: UserInfoResultData
```

### レスポンス型

```yaml
response: StageStartResultData
```

**レスポンス型の定義を確認:**
1. 同じYAMLファイルの `data` セクションを探す
2. `StageStartResult` という名前のデータ定義を探す（サフィックス `Data` は除く）

```yaml
data:
  - name: StageStartResult  # ← response の型名（Dataサフィックスなし）
    obscure: true
    params:
      - name: usrParameter
        type: UsrParameterData
      - name: usrInGameStatus
        type: UsrInGameStatusData
```

**ネストした型も確認:**
- `UsrParameterData` → `UsrParameter` のdata定義を探す
- `UsrInGameStatusData` → `UsrInGameStatus` のdata定義を探す

**他のYAMLファイルで定義されている場合:**

`UsrParameter` は User.yml で定義されている可能性があるので、そちらも確認します。

```bash
grep -r "UsrParameter" ../glow-schema/Schema/
```

## 実践例

### 例1: `/api/stage/end` の仕様確認

#### ステップ1: ファイルを開く
```bash
cat ../glow-schema/Schema/Stage.yml
```

#### ステップ2: API定義を探す
```yaml
api:
  - name: Stage
    actions:
      - name: End
        path: "/api/stage/end"
        params:
          - name: mstStageId
            type: string
          - name: inGameBattleLog
            type: InGameEndBattleLogData
        method: POST
        response: StageEndResultData
```

#### ステップ3: リクエストパラメータを確認
- `mstStageId`: string型、必須
- `inGameBattleLog`: InGameEndBattleLogData型（複雑なオブジェクト）

**InGameEndBattleLogData の定義を確認:**
```yaml
data:
  - name: InGameEndBattleLog
    obscure: true
    params:
      - name: defeatEnemyCount
        type: int
      - name: clearTimeMs
        type: int
      - name: partyStatus
        type: PartyStatusData[]  # ← 配列型
      ...
```

#### ステップ4: レスポンス型を確認
```yaml
data:
  - name: StageEndResult
    obscure: true
    params:
      - name: stageRewards
        type: StageRewardData[]
      - name: isEmblemDuplicated
        type: bool
      - name: userLevel
        type: UserLevelUpData
      - name: usrConditionPacks
        type: UsrConditionPackData[]
      ...
```

### 例2: 全ガチャAPIの確認

```bash
cat ../glow-schema/Schema/Gacha.yml | grep -A 10 "api:"
```

**出力（簡略版）:**
```yaml
api:
  - name: Gacha
    actions:
      - name: Prize
        path: "/api/gacha/prize"
        method: GET
      - name: Ad
        path: "/api/gacha/draw/ad"
        method: POST
      - name: Diamond
        path: "/api/gacha/draw/diamond"
        method: POST
```

## チェックリスト

APIを探す際に確認すべき項目:

- [ ] エンドポイントパスから適切なYAMLファイルを特定したか？
- [ ] `api` セクションで対象のアクションを見つけたか？
- [ ] `path`, `method`, `params`, `response` を確認したか？
- [ ] リクエストパラメータの型定義を `data` セクションで確認したか？
- [ ] レスポンス型の定義を `data` セクションで確認したか？
- [ ] ネストした型（*Data）の定義も確認したか？
- [ ] enum型のパラメータがあれば、その定義を確認したか？
