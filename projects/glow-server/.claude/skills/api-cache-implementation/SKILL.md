---
name: api-cache-implementation
description: |
  CacheKeyUtilとCacheClientManagerを使用したmomento/redisキャッシュの実装。以下の場合に使用: (1) グローバル・高速アクセス用のmomentoキャッシュ追加、(2) ランキングやSorted Sets用のredisキャッシュ実装、(3) ユーザーモデル(UsrModel)のキャッシュ実装、(4) CacheKeyUtilによるキャッシュキー命名定義、(5) TTLとエラーハンドリングの設定、(6) キャッシュテストの作成。基本パターン(set/get/delete)、Sorted Setパターン(ランキング、スコアベース)、UsrModelキャッシュパターンをカバー。キャッシュ実装、momento使用、redisキャッシュ追加、キャッシュ機能追加、キャッシュ戦略実装、キャッシュ設計時にトリガーされる。
---

# Implementing API Cache

momento/redisのキャッシュ実装を支援するスキルです。キャッシュ戦略、実装パターン、テスト方法を提供します。

## Instructions

### 1. キャッシュ戦略の選択

momento/redisの使い分け、キャッシュの適用範囲、TTLの設定方針を決定します。
参照: **[キャッシュ戦略ガイド](guides/cache-strategy.md)**

### 2. キャッシュキーの命名

CacheKeyUtilを使用したキーの命名規則と構造を実装します。
参照: **[キーの命名規則](guides/cache-key-naming.md)**

### 3. キャッシュの実装

CacheClientManagerを使用したキャッシュの取得・設定・削除を実装します。
参照リスト:
- **[基本実装パターン](patterns/basic-cache-pattern.md)** - set/get/delete
- **[Sorted Set実装パターン](patterns/sorted-set-pattern.md)** - ランキング、スコアベース
- **[UsrModelキャッシュパターン](patterns/usr-model-cache-pattern.md)** - ユーザーモデル用

### 4. テストの実装

キャッシュ処理のテストコードを作成します。
参照: **[テスト実装ガイド](guides/testing.md)**

## 参照ドキュメント

### ガイド
- **[キャッシュ戦略](guides/cache-strategy.md)** - momento/redisの使い分け、TTL設定方針
- **[キーの命名規則](guides/cache-key-naming.md)** - CacheKeyUtilの使用方法
- **[テスト実装](guides/testing.md)** - キャッシュテストの書き方

### パターン
- **[基本実装パターン](patterns/basic-cache-pattern.md)** - set/get/delete操作
- **[Sorted Set実装パターン](patterns/sorted-set-pattern.md)** - ランキング、スコアベース処理
- **[UsrModelキャッシュパターン](patterns/usr-model-cache-pattern.md)** - ユーザーモデル専用キャッシュ

### 実装例
- **[シンプルなキャッシュ実装](examples/simple-cache-example.md)** - MngCacheRepository
- **[ランキングキャッシュ実装](examples/ranking-cache-example.md)** - PvpCacheService
- **[ユーザーモデルキャッシュ実装](examples/usr-model-cache-example.md)** - UsrModelCacheRepository
