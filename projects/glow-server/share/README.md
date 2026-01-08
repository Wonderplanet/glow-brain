# Share

API側のコードを管理ツール側へ共有するためのパッケージです。

## 利用方法

composerの[ローカルパッケージ機能](https://qiita.com/suin/items/d24c2c0d8c221ccbc2f3)を利用して、このディレクトリ配下を管理ツール側でcomposerパッケージとしてinstallして利用することを想定しています。

管理ツール側のcomposer.jsonで既に設定済みで、かつセットアップ用のスクリプト（tools/setup.sh）内で`composer install`しているので、利用するにあたって追加の作業は特に必要ありません。

## 共有したいコードの追加方法

管理ツール側へ共有したいAPI側のディレクトリ等に対して、シンボリックリンクを作成することで、追加できます。composer.jsonの編集や、`composer install`などのコマンド実行は基本的には必要ないはずです。

注意点として、ディレクトリ構造がリンク先のAPI側と同じ状態になるよう、シンボリックリンクを作成する必要があります。例えば、`laravel-wp-framework/api/app/Http/Enums`に対するシンボリックリンクは、`share/Http/Enums`として作成して下さい。
