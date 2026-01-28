---
name: api-log-model-manager
description: |
  LogModelManagerを使用したログモデルの遅延永続化と共通メタデータ(logging_no)の自動設定ガイド。以下の場合に使用: (1) 遅延永続化によるログ保存(リクエスト完了時の一括保存)、(2) logging_noと共通メタデータの自動設定、(3) 新規ログテーブル(LogLogin、LogGacha、LogStage等)の追加、(4) LogModelを継承したログModelの作成、(5) ログRepositoryの作成、(6) LogModelManagerへの登録、(7) ログMigrationの作成。トランザクション管理と一括永続化を自動処理。ログ保存、LogModelManager使用、ログテーブル追加、logging_no設定、ログモデル追加、遅延ログ保存、ログ履歴記録時にトリガーされる。
---

# Using LogModelManager

LogModelManagerを使ってログデータを効率的に管理するためのガイドです。

## Instructions

### 1. LogModelManagerの役割を理解する

LogModelManagerがどのような役割を持ち、なぜ使用するのかを理解します。
参照: **[architecture.md](architecture.md)**

### 2. 既存ログテーブルの使い方を確認する

既存のログテーブル（LogLogin等）を参考に、基本的な使用パターンを学びます。
参照: **[usage-patterns.md](usage-patterns.md)**

### 3. 新規ログテーブルを追加する

新しいログテーブルが必要な場合、Model、Repository、Migrationを作成します。
参照: **[implementation-guide.md](implementation-guide.md)**

## 参照ドキュメント

- **[architecture.md](architecture.md)** - LogModelManagerのアーキテクチャと設計思想
- **[usage-patterns.md](usage-patterns.md)** - 基本的な使用パターンと具体例
- **[implementation-guide.md](implementation-guide.md)** - 新規ログテーブル追加の実装手順
