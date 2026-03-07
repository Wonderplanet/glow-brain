# Laravel Wonderplanet Framework

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Laravelアプリケーションのサンプルプロジェクトです。

新規で開発を始めるにあたって、以下の内容に関して参考になるかと思います。
- コンテナを用いたLaravelアプリケーションの実行環境の構築方法
- 開発ツールの導入、設定および利用方法
  - テスト: PHPUnit
  - コーディング規約のチェック: PHP_CodeSniffer（[slevomat/coding-standard](https://github.com/slevomat/coding-standard)）
  - 静的解析: PHPStan（[Larastan](https://github.com/nunomaduro/larastan)）
  - アーキテクチャテスト: [Deptrac](https://qossmic.github.io/deptrac/)
  - デバッグ: Xdebug
- クリーンアーキテクチャやモジュラーモノリスを意識した実装方法
- テストの実装方法
- 共通機能の実装方法（将来的にパッケージ化する予定です）
  - ユーザー認証
  - アカウント管理
- 管理ツールの構築方法

## 📚 ドキュメント

プロジェクトの設計・仕様・開発ガイドなどのドキュメントは [docs/](docs/) ディレクトリに格納されています。

### 主要ドキュメント

- **[docs/README.md](docs/README.md)** - ドキュメント全体のインデックス
- **[docs/sdd-v2/](docs/sdd-v2/)** - SDD v2 設計ドキュメント（機能要件定義）
- **[docs/coderabbit-functional-requirements-check.md](docs/coderabbit-functional-requirements-check.md)** - CodeRabbitによる機能要件チェック設定

### CodeRabbitによる自動レビュー

このプロジェクトでは、**CodeRabbitがPRレビュー時に機能要件の実装状況を自動チェック**します。

**効果：**
- ✅ 機能要件の漏れを自動検出
- ✅ レビュイーはチームレビュー前に要件漏れに気付ける
- ✅ レビュワーは非機能要件のレビューに集中できる
- ✅ バグが少なく、将来の追加/変更に強い実装成果物

詳細は [docs/coderabbit-functional-requirements-check.md](docs/coderabbit-functional-requirements-check.md) を参照してください。

## ディレクトリ構成
大まかなディレクトリ構成は以下の通りです。
```
├──admin/                   : 管理ツール
├──api/
│   ├─app/
│   │  ├─Domain/
│   │  │  ├─DeviceLink/
│   │  │  │  ├─Eloquent/
│   │  │  │  │  ├─Models/   : Eloquentモデル
│   │  │  │  │  └─Services/ : Eloquentモデルに依存するサービス
│   │  │  │  ├─Entities/    : エンティティ
│   │  │  │  ├─Services/    : サービス
│   │  │  │  └─UseCases/    : ユースケース
│   │  │  └─User/
│   │  └─Http/
│   │     └─Controllers/    : コントローラー
│   ├──database/            : DBマイグレーション用のファイルなど
│   ├──routes/              : ルーター
│   └──tests/               : テスト
├──docker/                  : Dockerfileなど
└──tools/                   : ツール
```

## ローカル環境の構築

### 1. Dockerのインストール

Docker上に環境を構築するため、事前にDockerをインストールしておく必要があります

### 2. Gitクレデンシャル環境変数の設定 

管理ツール上のマスターデータリポジトリの管理のためにプロジェクトルートにある.envファイルをコピーして設定します。

```
cp .env.sample .env
```

コピーした.envファイルに、ユーザートークン認証もしくはGIT_SSH_KEYのどちらかの設定を行います。
トークンは[こちら](https://github.com/settings/tokens)のページから発行できます。

```
### Git設定 ###
# adminのDockerFileのビルド時に.envからGit設定を注入します
# GIT_USER_IDとGIT_USER_TOKENを指定するとユーザートークンでの認証、
# GIT_SSH_KEYを指定するとDeploy Keyでの認証を行います

# ユーザートークン認証（HTTPS接続）
GIT_USER_ID=XXXXX（どちらかを設定）
GIT_USER_TOKEN=XXXXX（どちらかを設定）

# Deploy Key認証（SSH接続）
GIT_SSH_KEY=YYYYY（どちらかを設定）
```

### 3. セットアップ用のスクリプトの実行

以下の場所に存在するスクリプトを実行して、しばらく待つとローカル環境が起動します。
```
./tools/setup.sh
```


### 4. アプリケーションの起動確認

http://localhost:8080/ へアクセスして、ページが表示されるか確認します。

http://localhost:8081/admin へアクセスして、管理ツールページのログイン画面が表示されるか確認します。

## 開発ツールの使い方

開発を進める上で、コンテナ上で稼働しているアプリケーションに対して様々な操作（起動、停止、デバッグ、テスト、パッケージの追加など）が必要になります。

その度に、長いコマンドを入力したり、コンテナの中に入って作業をするのは面倒なので、手軽に操作するためのCLIツールを用意しています。

```
# ツールの実行（ヘルプを表示）
./tools/bin/sail-wp

# sailという名前で、エイリアスを登録
echo 'alias sail=<path_to_this_repo>/tools/bin/sail-wp' >> ~/.zprofile

# .bash_profileの再読み込み
source ~/.zprofile

# エイリアスによるツールの実行（ヘルプを表示）
sail
```

このツールは、[Laravel Sail](https://readouble.com/laravel/9.x/ja/sail.html)をベースに独自の改修を加えたものです。入力するのが面倒なコマンドは、こちらに登録しておくと良いでしょう。

以降は、このsailコマンドを介した、様々な開発ツールの利用方法を説明します。

### コンテナの管理

このリポジトリに含まれる、Laravelアプリケーションや各種開発ツールはコンテナ上で動作するため、事前に起動しておく必要があります。
```
# コンテナの起動
sail up -d

# コンテナの停止
sail down

# コンテナの中に入る
sail bash
```

### コードのチェック

以下のコマンドにより、3つのチェック（コーディング規約のチェック、静的解析、テスト）がまとめて実行されます。
```
sail check
```

それぞれのチェックを個別に実行することも可能です。
```
# コーディング規約違反の検出
sail phpcs

# コーディング規約違反の修正
sail phpcbf

# 静的解析の実行
sail phpstan

# アーキテクチャテストの実行
sail deptrac

# テストの実行（+カバレッジの表示）
sail test

# 管理画面テストの実行（+カバレッジの表示）
sail admin test
```

コミットやプッシュする前に、これらのコマンドを実行してエラーが発生していないか確認するようにしましょう。

## 管理ツールの使い方

別途、[README](/admin/)を用意しているので、そちらをご参照下さい。
