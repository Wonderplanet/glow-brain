# ハンズオン: テスト実行・自動修正 (`/project:api-test`)

このハンズオンでは、Claude Codeを使ってPHPUnitテストを実行し、失敗したテストを自動修正する方法を学びます。

## このハンズオンで学べること

- `/project:api-test` コマンドの基本的な使い方
- テスト失敗時の自動修正フロー
- フィルタを使った特定テストの実行
- テスト関連のスキル連携

## 所要時間

約15分

---

## 事前準備

### 1. Dockerコンテナが起動していることを確認

```bash
./tools/bin/sail-wp ps
```

コンテナが起動していない場合：
```bash
./tools/bin/sail-wp up -d
```

### 2. Claude Codeが起動していることを確認

ターミナルでClaude Codeセッションが開始されていることを確認してください。

---

## ハンズオン手順

### Step 1: 全テストを実行する

最もシンプルな使い方です。Claude Codeに以下のように入力してください：

```
/project:api-test
```

**何が起こるか：**
1. `sail phpunit` コマンドが実行される
2. 全テストの結果が表示される
3. 失敗（Error/Failure/Warning）があれば自動修正が開始される
4. 全テストがパスするまで繰り返される

**期待される出力例：**
```
PHPUnit 10.x.x

............................                              28 / 28 (100%)

Time: 00:05.123, Memory: 128.00 MB

OK (28 tests, 45 assertions)
```

### Step 2: 特定のテストクラスを実行する

特定のテストクラスだけを実行したい場合：

```
/project:api-test StageEndTest
```

**何が起こるか：**
1. `sail phpunit --filter "StageEndTest"` が実行される
2. `StageEndTest` クラスのテストのみが実行される

### Step 3: 複数のテストクラスを実行する

複数のテストを同時に実行したい場合は、パイプ（`|`）で区切ります：

```
/project:api-test StageEndTest|GachaTest|MissionTest
```

**何が起こるか：**
1. `sail phpunit --filter "StageEndTest|GachaTest|MissionTest"` が実行される
2. 指定した3つのテストクラスが実行される

### Step 4: 特定のテストメソッドを実行する

テストメソッド名でフィルタすることもできます：

```
/project:api-test testStageEndSuccess|testStageEndFailure
```

---

## 実践シナリオ

### シナリオA: テストが失敗した場合

以下のような失敗が発生したとします：

```
FAILURES!
Tests: 5, Assertions: 10, Failures: 1.

1) Tests\Feature\StageEndTest::testStageEndSuccess
Failed asserting that 400 matches expected 200.
```

**Claude Codeの動作：**
1. 失敗内容を分析
2. 原因を特定（コントローラーの実装ミス、テストデータの問題など）
3. 修正案を提示または自動修正
4. 再度テストを実行
5. 成功するまで繰り返し

**あなたがすること：**
- 基本的には見守るだけでOK
- 修正方針について質問されたら回答する
- 複数の修正案がある場合は選択する

### シナリオB: 複数のテストが失敗した場合

```
FAILURES!
Tests: 10, Assertions: 20, Failures: 3.
```

**Claude Codeの動作：**
1. TODOリストに失敗したテストを追加
2. 1つずつ順番に修正
3. 各修正後にテストを実行して確認
4. 全て完了するまで繰り返し

---

## よくある質問と対処法

### Q1: テスト実行が途中で止まる

**対処法：**
```
「テストの続きを実行して」
```
または
```
/project:api-test
```
を再度実行

### Q2: 修正内容を確認したい

**対処法：**
```
「修正内容を説明して」
「どのファイルを変更したか教えて」
```

### Q3: 自動修正ではなく手動で直したい

**対処法：**
```
「テストを実行して結果だけ教えて。修正は自分でする」
```

### Q4: 特定のディレクトリのテストだけ実行したい

**対処法：**
```
「Tests/Feature/Stage配下のテストを全て実行して」
```

---

## 関連するスキルとの連携

### api-test-implementation スキル

新しいテストを書きたい場合：

```
「StageStartControllerのテストを書いて」
```

Claude Codeは `api-test-implementation` スキルを参照して、プロジェクトの規約に沿ったテストを作成します。

### api-test-runner スキル

テスト実行の詳細なデバッグが必要な場合：

```
「api-test-runnerスキルを使ってテストのデバッグをして」
```

---

## コマンドを使わない方法

`/project:api-test` コマンドを使わずに、自然言語で指示することもできます：

```
「PHPUnitテストを実行して、失敗していたら修正して」
「StageEndTestを実行してグリーンにして」
「テストを回して全部通るようにして」
```

Claude Codeは適切なスキルを読み込んで、同様の処理を行います。

---

## まとめ

| 目的 | コマンド/指示 |
|------|--------------|
| 全テスト実行 | `/project:api-test` |
| 特定クラス実行 | `/project:api-test TestClassName` |
| 複数クラス実行 | `/project:api-test Test1\|Test2\|Test3` |
| メソッド指定 | `/project:api-test testMethodName` |
| 自然言語での指示 | 「テストを実行して失敗を修正して」 |

---

## 次のステップ

- [ハンズオン: コード品質チェック](./02-sail-check-fixer-hands-on.md) - PR作成前の必須作業を学ぶ
- [api-test-implementation スキル](../skills/api-test-implementation/SKILL.md) - テストの書き方を学ぶ
