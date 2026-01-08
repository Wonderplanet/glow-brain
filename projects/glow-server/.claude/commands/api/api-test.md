# api-test

PHPUnitテストを実行し、Error/Failure/Warningなど が出た場合は修正して、対象のテスト全てが正常に通る状態にするコマンドです。

## 使用方法

```
/project:api-test
```

全てのテストを実行:
```
/project:api-test
```

特定のテストファイルまたはメソッドをフィルタリング:
```
/project:api-test UserTest|LoginTest
```

## 実行内容

引数: $ARGUMENTS

### 1. 前処理
- 引数の検証（フィルタ条件がある場合）
- 作業ディレクトリがglow-serverであることを確認

### 2. メイン処理
- 引数がない場合: `sail phpunit` で全テストを実行
- 引数がある場合: `sail phpunit --filter "引数1|引数2|..."` で特定のテストを実行
- テスト結果を解析してError/Failure/Warningなどを検出

### 3. 異常の対応
- Error/Failure/Warningなどが発生した場合:
  - Error/Failure/Warningなどのメッセージを詳細に分析
  - 修正対象を特定しTODOリストに追加
  - 修正対象が複数ある場合は、各項目を個別のTODOアイテムとして追加
  - 修正対象を1つずつ処理（修正→テスト→完了マーク）
  - 該当するテストファイルやソースコードを特定
  - 自動修正を実行
  - 再度テストを実行して結果を確認
- 全てのテストが正常に通るまで繰り返し

### 4. 後処理
- 最終的なテスト結果の表示
- 修正した内容の報告

## 注意事項

- 作業ディレクトリの移動は禁止
- sailを使用してテストを実行
  - ./vendor/bin/sail は使わない
  - 直接 sail を使う (作業ディレクトリの移動は不要)
- Error/Failure/Warningなどが発生した場合は自動修正を試行
- 複数のテストフィルタはパイプ（|）で区切る
- テストファイル名やメソッド名の部分一致で検索
- 修正対象が複数ある場合は、TODOリストを活用して確実に1つずつ処理
- 各修正後は必ずテストを実行して結果を確認してから次の修正に進む
- 重要：正常に通っていないテストが1つでもある場合、勝手に作業を終了してはいけません

## 使用例

```bash
# 全テスト実行
/project:api-test

# 特定のテストクラスのみ実行
/project:api-test UserTest

# 複数のテストを実行
/project:api-test UserTest|LoginTest|RegisterTest

# 特定のテストメソッドを実行
/project:api-test testUserLogin|testUserRegister
```