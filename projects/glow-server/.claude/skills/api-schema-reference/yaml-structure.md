# YAMLファイルの構造

glow-schemaのYAMLファイルは、クライアントとサーバー間のAPI仕様を定義しています。

## 基本構造

各YAMLファイルは3つの主要セクションで構成されています:

```yaml
enum:
  - name: 列挙型名
    params:
      - name: 値1
      - name: 値2

data:
  - name: データ型名
    obscure: true
    params:
      - name: フィールド名
        type: 型

api:
  - name: API名
    actions:
      - name: アクション名
        path: "/api/path"
        params:
          - name: パラメータ名
            type: 型
        method: GET|POST
        response: レスポンス型
```

## 1. enum セクション

列挙型の定義。定数値のセットを表します。

### 例: Stage.yml

```yaml
enum:
  - name: Difficulty
    params:
      - name: Normal
      - name: Hard
      - name: Extra
```

**PHP実装での対応:**
- Enum クラスとして実装される場合がある
- バリデーションでは文字列として扱われることが多い

## 2. data セクション

データ構造の定義。テーブル、レスポンス型、ネストしたオブジェクトを定義します。

### データの種類（サフィックス規則）

#### Mst* (マスターデータ)
```yaml
- name: MstStage
  obscure: true
  params:
    - name: id
      type: string
    - name: stageNumber
      type: int
    - name: costStamina
      type: int
```

**用途:** ゲームの基本設定データ（マスターテーブル）

#### Usr* (ユーザーデータ)
```yaml
- name: UsrParameter
  obscure: true
  params:
    - name: level
      type: int
    - name: exp
      type: int
    - name: stamina
      type: int
```

**用途:** プレイヤー個別のデータ（ユーザーテーブル）

#### *Result (結果型)
```yaml
- name: StageStartResult
  obscure: true
  params:
    - name: usrParameter
      type: UsrParameterData
    - name: usrInGameStatus
      type: UsrInGameStatusData
```

**用途:** API レスポンスのトップレベル型

#### *Data (データ型)
```yaml
- name: UsrParameterData
  obscure: true
  params:
    - name: level
      type: int
    - name: exp
      type: int
```

**用途:** レスポンス内でネストされるデータ型。通常は `*Result` 型の `params` 内で使用される。

### obscure フラグ

```yaml
- name: MstStage
  obscure: true  # ← このフラグ
  params:
    ...
```

**意味:** このデータ型がスキーマビルダーによってコード生成されることを示します。

### impl_entity フラグ

```yaml
- name: OprGacha
  obscure: true
  impl_entity: true  # ← このフラグ
  params:
    ...
```

**意味:** このデータ型がEntityクラスとしても実装されることを示します。

## 3. api セクション

APIエンドポイントの定義。

### 基本構造

```yaml
api:
  - name: Stage  # API名（通常は機能名）
    actions:
      - name: Start  # アクション名（メソッド名）
        path: "/api/stage/start"  # エンドポイントパス
        params:  # リクエストパラメータ
          - name: mstStageId
            type: string
          - name: partyNo
            type: int
          - name: isChallengeAd
            type: bool
        method: POST  # HTTPメソッド
        response: StageStartResultData  # レスポンス型（dataセクションで定義されたもの）
```

### パラメータなしのAPI

```yaml
- name: Info
  path: "/api/user/info"
  params:  # 空のリスト
  method: GET
  response: UserInfoResultData
```

**Controller実装:** `params` が空の場合でも、`$request->validate([])` を記述することがあります。

### レスポンス型の特殊ケース

```yaml
response: HeadOK
```

**HeadOK:** 成功のみを返す特殊なレスポンス型。通常は 200 OK で空のボディを返します。

## YAMLとコードの対応

### 例: /api/stage/start

**YAML定義 (Stage.yml):**
```yaml
api:
  - name: Stage
    actions:
      - name: Start
        path: "/api/stage/start"
        params:
          - name: mstStageId
            type: string
          - name: partyNo
            type: int
          - name: isChallengeAd
            type: bool
        method: POST
        response: StageStartResultData

data:
  - name: StageStartResult
    obscure: true
    params:
      - name: usrParameter
        type: UsrParameterData
      - name: usrInGameStatus
        type: UsrInGameStatusData
```

**PHP Controller実装:**
```php
public function start(StageStartUseCase $useCase, Request $request): JsonResponse
{
    $validated = $request->validate([
        'mstStageId' => 'required',      // ← YAMLのparams
        'partyNo' => 'required',         // ← YAMLのparams
        'isChallengeAd' => 'required|boolean',  // ← YAMLのparams
    ]);

    $resultData = $useCase->exec(
        $this->request->user(),
        $validated['mstStageId'],
        $partyNo,
        $isChallengeAd,
    );

    return $this->responseFactory->createStartResponse($resultData);
    // ↑ StageStartResultData 型のレスポンスを返す
}
```

**ResponseFactory実装:**
```php
public function createStartResponse(StageStartResult $result): JsonResponse
{
    return response()->json([
        'usrParameter' => $this->responseDataFactory->createUsrParameterData($result->usrParameter),
        'usrInGameStatus' => $this->responseDataFactory->createUsrInGameStatusData($result->usrInGameStatus),
    ]);
}
```

## チェックリスト

YAMLを読む際に確認すべき項目:

- [ ] このAPIはどのYAMLファイルに定義されているか？
- [ ] `api` セクションで `path`, `params`, `method`, `response` を確認したか？
- [ ] リクエストパラメータの型と必須/任意を確認したか？
- [ ] レスポンス型の `data` セクションを確認したか？
- [ ] レスポンス内でネストされる他の `*Data` 型を確認したか？
- [ ] enum 型のパラメータがあれば、その定義を確認したか？
