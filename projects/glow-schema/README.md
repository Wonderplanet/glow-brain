Schema
===========

SchemaBuilderはymlの定義ファイルからサーバー・通信間のクラスやデータ構造ファイルを自動生成するシステムです。


.evnファイルの設定
--------
.env.sampleをコピーして各プロジェクトの環境に合わせてください。

```
cp .env.sample .env
```


Docker
-------

docker/docker-composeでの動作をサポートしています。

Docker Desktopのライセンス申請をして環境を整えるか、
colima等をInstallしてください。


### Colimaの導入
https://wonderplanet.esa.io/posts/6134

```sh
brew upgrade
brew install docker docker-compose
brew install colima
```

```sh
colima start
```

### 初回セットアップ

以下を実行してください。bundle installなどがされます

```sh
docker-compose --profile setup up
```

### スキーマファイルの生成 

```sh
docker-compose --profile build up
```

or 

```sh
./update_schema.sh 
```
