---
name: api-request-validation
description: |
  glow-schemaのYAML定義からLaravelバリデーションルールへのリクエストパラメータバリデーション実装。以下の場合に使用: (1) 新しいAPIエンドポイントのリクエストバリデーション実装、(2) 既存APIへのパラメータバリデーション追加、(3) YAML型定義のLaravelバリデーションルールへの変換、(4) 配列やオブジェクトのネストしたバリデーション実装。required/nullable (required/nullable)、型チェック (string/int/bool/array)、ネストバリデーション、カスタムルールをサポート。型マッピングパターンを提供: string→'required', int→'required', bool→'required|boolean', string?→'nullable', Type[]→'array'。Controllerでの実装パターンを含む。 (project)
---

# Implementing API Request Validation

glow-schemaのYAML定義をもとに、Controllerでリクエストパラメータのバリデーションを実装するためのガイドです。

## Instructions

### 1. YAML定義を確認する

まず、glow-schemaで対象APIの `params` を確認します。
参照: **[api-schema-reference](../api-schema-reference/SKILL.md)** スキル

```yaml
api:
  - name: Stage
    actions:
      - name: Start
        params:
          - name: mstStageId
            type: string
          - name: partyNo
            type: int
          - name: isChallengeAd
            type: bool
```

### 2. 型マッピングを理解する

YAML型からLaravelバリデーションルールへの変換ルールを確認します。
参照: **[type-mapping.md](type-mapping.md)**

- `string` → `'required'`
- `int` → `'required'`
- `bool` → `'required|boolean'`
- `string?` → `'nullable'`
- `Type[]` → `'array'`

### 3. Controllerでバリデーションを実装する

実装パターンに従ってバリデーションコードを記述します。
参照: **[implementation-pattern.md](implementation-pattern.md)**

```php
$validated = $request->validate([
    'mstStageId' => 'required',
    'partyNo' => 'required',
    'isChallengeAd' => 'required|boolean',
]);
```

### 4. 実装例を参考にする

実際のコード例を確認して、正しい実装パターンを理解します。
参照: **[examples.md](examples.md)**

## 参照ドキュメント

- **[type-mapping.md](type-mapping.md)** - YAML型とLaravelバリデーションルールの対応表
- **[implementation-pattern.md](implementation-pattern.md)** - Controllerでのバリデーション実装パターン
- **[examples.md](examples.md)** - 実装例とよくあるケース

## 関連スキル

このスキルでバリデーションを実装した後、以下のスキルでレスポンス実装を進めてください:
- **api-response** - レスポンス実装

バリデーション実装前に、以下のスキルでYAML仕様を確認してください:
- **api-schema-reference** - スキーマ仕様確認
