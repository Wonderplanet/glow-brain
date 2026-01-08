# Wonderplanet Admin

<p align="center">
    <img src="https://user-images.githubusercontent.com/41773797/131910226-676cb28a-332d-4162-a6a8-136a93d5a70f.png" alt="Banner" style="width: 100%; max-width: 800px;" />
</p>

管理ツールのサンプルプロジェクトです。

Laravel上で管理ツールを構築するためのライブラリとして、[Filament](https://filamentphp.com/)を使用しています。

## 使い方

ローカル環境の構築方法や、基本的な開発ツールの使い方は[API側](../)と同じなので、まずはそちらをご確認下さい。

ただし、管理ツール側に対してsailコマンドを実行する場合は、下記のように「sail admin ${COMMAND}」という形で実行する点が異なります。

```
sail admin artisan migrate
```

### 管理ツールへのログイン

ローカル環境の場合、 http://localhost:8081/admin にアクセスするとログイン画面が表示されます。

初期ユーザーとして管理者権限を持つユーザーが作られており、下記でログインできます。

```
メールアドレス: admin@wonderpla.net
パスワード: admin
```

初期ユーザーが作成されておらず上記の情報でログインできない場合は以下のコマンドを実行してください
```
sail fadmin artisan db:seed
```

## ページの作成方法
### DBへのCRUD操作を目的としたページの場合

Eloquentモデルを介した、DBへのCRUD操作を行うためのページについては、[Resource](https://filamentphp.com/docs/2.x/admin/resources/getting-started)という仕組みで手軽に作成できます。

「App\Models\Customer」というモデルに対するResourceの作成方法は下記の通りです。

```
sail admin artisan make:filament-resource Customer
```

このコマンドを実行すると、app/Filament/Resourcesディレクトリの中に下記のファイルが作成されます。

```
.
+-- CustomerResource.php
+-- CustomerResource
|   +-- Pages
|   |   +-- CreateCustomer.php
|   |   +-- EditCustomer.php
|   |   +-- ListCustomers.php
```

この段階で、既にサイドバーのメニューに「Customer」が追加されていて、クリックするとページが表示されます。

ただし、ページ上に列情報などが表示されるようにするためには、上記のファイルを編集する必要があるので、本プロジェクトに含まれる既存のファイルや[Filamentのドキュメント](https://filamentphp.com/docs/2.x/admin/resources/getting-started)を参考にして下さい。

ちなみに、[こちら](https://github.com/askdkc/filamentphp_howto#filament用管理リソースを作成する)の日本語の解説記事も分かりやすかったです。

### DBへのCRUD操作以外のことを目的としたページの場合

TODO
