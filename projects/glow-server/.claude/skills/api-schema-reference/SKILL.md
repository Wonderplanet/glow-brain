---
name: api-schema-reference
description: |
  glow-schemaのYAML定義(APIエンドポイント、データ構造、列挙型)を読み取るためのリファレンスガイド。以下の場合に使用:(1) API仕様の確認(path、method、params、response)、(2) データ構造定義の参照(テーブル、レスポンス型)、(3) Enum定義の確認、(4) 型システムルールの理解(基本型、オプショナル?、配列[]、サフィックス規則Mst*、Usr*、*Result、*Data)。API実装前の仕様確認、パラメータ定義の検証、レスポンス構造の確認、データ型の確定時に使用する。
---

# Reading API Schema Reference

API実装前の仕様確認や既存API調査時に、glow-schemaのYAML定義を正しく読むためのリファレンスです。

## Instructions

### 1. YAMLファイルの構造を理解する

glow-schemaのYAMLは3つの主要セクションで構成されています。
参照: **[yaml-structure.md](yaml-structure.md)**

- `enum`: 列挙型の定義
- `data`: データ構造の定義（テーブル、レスポンス型）
- `api`: APIエンドポイントの定義

### 2. APIエンドポイントを探す

実装したいAPIの仕様を見つける方法を確認します。
参照: **[finding-apis.md](finding-apis.md)**

- ファイルの探し方（機能別に分類）
- API定義の読み方（path, params, method, response）

### 3. 型システムを理解する

YAML内のデータ型とPHP実装との対応を理解します。
参照: **[type-system.md](type-system.md)**

- 基本型（string, int, bool等）
- オプショナル型（`?`サフィックス）
- 配列型（`[]`サフィックス）
- サフィックス規則（Mst*, Usr*, *Result, *Data）

## 参照ドキュメント

- **[yaml-structure.md](yaml-structure.md)** - YAMLファイルの構造とセクションの説明
- **[finding-apis.md](finding-apis.md)** - APIの探し方とファイル構成
- **[type-system.md](type-system.md)** - 型システムとサフィックス規則

## 関連スキル

このスキルで仕様を確認した後、以下のスキルで実装を進めてください:
- **api-request-validation** - リクエストバリデーション実装
- **api-response** - レスポンス実装
