## ディレクトリ説明
本リポジトリはapiディレクトリのAPI開発とadminディレクトリの管理画面開発を行うためのものです。
shareディレクトリは、APIと管理画面の両方で使用される共通のコードを格納するために使用されます。
ただしapiディレクトリから必要なものがシンボリックリンクで配置されるのでAi側から参照・変更・削除する必要はありません。

## コマンド説明
開発コードはDockerコンテナ内で実行されます。
実行を簡略化するためlaravel sailコマンドを拡張したコマンドを{repository-root}/tools/bin/sail-wpに配置しています。
sailコマンドを実行したい場合はこちらを利用してください。
またapi開発をする場合はsail-wp, 管理画面開発をする場合はsail-wp adminで実行できます。
例：
- API開発でartisanコマンドを実行する場合
```bash
./tools/bin/sail-wp artisan migrate
```

- 管理画面開発でartisanコマンドを実行する場合
```bash
./tools/bin/sail-wp admin artisan migrate
```

またテストを実行したい場合は同様に
- API開発でテストを実行する場合
```bash
./tools/bin/sail-wp test
```

- 管理画面開発でテストを実行する場合
```bash
./tools/bin/sail-wp admin test
```

で実行できます
