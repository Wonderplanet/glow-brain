---
name: api-usr-model-cache-repository
description: glow-serverのユーザーデータキャッシュ用UsrModelCacheRepositoryとUsrModelManagerの実装。SingleCacheRepository（1ユーザー=1レコード）とMultiCacheRepository（1ユーザー=Nレコード）パターン、saveModels、cachedGetメソッド、キャッシュ同期、N+1クエリ防止をサポート。UsrModelCacheRepository実装、ユーザーキャッシュ追加、saveModels実装、cachedGet使用、CacheRepository作成、新規usr_*テーブル追加、キャッシュ列変更時に使用。 (project)
---

# UsrModelCacheRepository & UsrModelManager

UsrModelCacheRepositoryとUsrModelManagerを使ったユーザーデータキャッシュ機構の実装ガイドです。

## Instructions

### 1. 基本概念を理解

UsrModelManagerのキャッシュ機構、SingleとMultiの使い分けを確認します。
参照: **[overview.md](overview.md)**

### 2. 新規テーブル追加・列変更時の実装

新規テーブル追加時、列追加時、列変更時のsaveModels実装手順を確認します。
参照: **[save-models-implementation.md](save-models-implementation.md)**

### 3. cachedGet系メソッドの使い分け

SingleとMulti RepositoryでのcachedGetメソッドの使い分けパターンを確認します。
参照リスト:
- **[cached-get-single.md](cached-get-single.md)** - SingleCacheRepositoryのcachedGetOne
- **[cached-get-multi.md](cached-get-multi.md)** - MultiCacheRepositoryのcachedGetAll/Many/OneWhere

### 4. キャッシュ同期パターン

syncModel/syncModelsの使い方とキャッシュ更新タイミングを確認します。
参照: **[cache-sync-patterns.md](cache-sync-patterns.md)**

## 参照ドキュメント

### ガイド
- **[overview.md](overview.md)** - UsrModelManagerの仕組みと基本概念
- **[save-models-implementation.md](save-models-implementation.md)** - saveModelsの実装手順と注意点
- **[cached-get-single.md](cached-get-single.md)** - SingleCacheRepositoryのcachedGetOneメソッド
- **[cached-get-multi.md](cached-get-multi.md)** - MultiCacheRepositoryのcachedGet系メソッド
- **[cache-sync-patterns.md](cache-sync-patterns.md)** - キャッシュ同期のベストプラクティス

### 実装例
- **[example-single-repository.md](examples/example-single-repository.md)** - UsrUserRepository実装例
- **[example-multi-repository.md](examples/example-multi-repository.md)** - UsrExchangeLineupRepository実装例
