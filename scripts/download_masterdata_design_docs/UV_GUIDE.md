# uv使い方ガイド

## uvとは？

[uv](https://github.com/astral-sh/uv)は、Rustで書かれた**超高速なPythonパッケージマネージャー**です。

### メリット

- ⚡ **圧倒的な速度**: pipより10-100倍速いインストール
- 🔒 **完全な再現性**: `uv.lock`で厳密な依存関係管理
- 🎯 **シンプルな実行**: `uv run`で仮想環境の有効化が不要
- 🛠️ **統合ツール**: プロジェクト管理から実行まで1つのコマンドで完結
- 📦 **pipと互換**: requirements.txtやpyproject.tomlをそのまま使える

### pipとの比較

```
従来のpip + venv:
1. python3 -m venv venv
2. source venv/bin/activate
3. pip install -r requirements.txt
4. python script.py
5. deactivate

uvの場合:
1. uv sync              # 依存関係インストール
2. uv run script.py     # 実行（環境有効化不要）
```

---

## uvのインストール

### macOS / Linux

```bash
curl -LsSf https://astral.sh/uv/install.sh | sh
```

### Windows

```powershell
powershell -c "irm https://astral.sh/uv/install.ps1 | iex"
```

### インストール確認

```bash
uv --version
```

---

## 基本的な使い方

### 1. プロジェクトのセットアップ

```bash
# プロジェクトディレクトリに移動
cd scripts/download_masterdata_design_docs

# 依存関係をインストール（.venvが自動作成される）
uv sync
```

**実行後:**
```
scripts/download_masterdata_design_docs/
├── .venv/          # ← 自動作成される仮想環境
│   ├── bin/
│   ├── lib/
│   └── ...
├── uv.lock         # ← ロックファイル（厳密な依存関係）
├── pyproject.toml
└── download_masterdata_design_docs.py
```

### 2. スクリプトの実行

#### 方法1: `uv run`（推奨）

仮想環境の有効化は**不要**です。

```bash
uv run download_masterdata_design_docs.py "スプレッドシートURL"
```

`uv run`は自動的に：
1. `.venv`環境を検出
2. 必要に応じて依存関係をインストール
3. 仮想環境内でスクリプトを実行

#### 方法2: 仮想環境を手動で有効化

従来のvenvと同じ方法も使えます。

```bash
# 有効化
source .venv/bin/activate  # macOS/Linux
# または
.venv\Scripts\activate     # Windows

# スクリプト実行
python3 download_masterdata_design_docs.py "スプレッドシートURL"

# 無効化
deactivate
```

---

## 主要コマンド一覧

### プロジェクト管理

```bash
# 依存関係をインストール（pyproject.tomlから）
uv sync

# 依存関係を更新（最新バージョンに）
uv sync --upgrade

# 新しいパッケージを追加
uv add <package-name>

# パッケージを削除
uv remove <package-name>
```

### スクリプト実行

```bash
# Pythonスクリプトを実行
uv run script.py

# Pythonコマンドを実行
uv run python -c "print('Hello, uv!')"

# 対話型シェルを起動
uv run python
```

### 仮想環境管理

```bash
# 仮想環境を作成（通常は自動作成されるため不要）
uv venv

# 仮想環境を削除
rm -rf .venv
```

---

## 日常的な使い方

### 毎回のワークフロー

```bash
# 1. プロジェクトディレクトリに移動
cd scripts/download_masterdata_design_docs

# 2. スクリプトを実行（環境有効化不要）
uv run download_masterdata_design_docs.py "URL"
```

たったこれだけ！仮想環境の有効化・無効化は不要です。

### 依存関係を更新したい場合

```bash
# pyproject.tomlを編集してから
uv sync
```

### 新しいライブラリを追加したい場合

```bash
# 方法1: コマンドで追加（pyproject.tomlが自動更新される）
uv add <package-name>

# 方法2: pyproject.tomlを手動編集してから
uv sync
```

---

## よくある質問

### Q: 仮想環境を毎回作る必要がある？

**A: いいえ**。`.venv`ディレクトリは一度作成されれば、次回以降も使い回されます。

```bash
# 初回
uv sync  # .venvが作成される

# 2回目以降
uv run script.py  # 既存の.venvを使用
```

### Q: `uv run`と`source .venv/bin/activate`の違いは？

**A: どちらも同じ仮想環境を使いますが、`uv run`の方が便利です。**

| 方法 | メリット | デメリット |
|------|---------|----------|
| `uv run` | 有効化不要、自動で依存関係チェック | - |
| `source .venv/bin/activate` | 従来のvenvと同じ操作感 | 有効化・無効化が必要 |

### Q: `uv.lock`とは？

**A: 厳密な依存関係のロックファイルです。**

- `pyproject.toml`: 緩い依存関係（例: `requests>=2.31.0`）
- `uv.lock`: 厳密なバージョン（例: `requests==2.31.0`と全ての依存パッケージ）

`uv.lock`をGitにコミットすることで、チーム全員が完全に同じ環境を再現できます。

### Q: pipで入れたパッケージはどうなる？

**A: uvとpipは独立しています。**

- `uv sync`でインストールしたパッケージは`.venv`に入ります
- システムのpipでインストールしたパッケージとは別です

### Q: requirements.txtは使える？

**A: 使えますが、pyproject.tomlの方が推奨です。**

```bash
# requirements.txtから依存関係をインストール
uv pip install -r requirements.txt

# pyproject.tomlを使用（推奨）
uv sync
```

### Q: `.venv`を削除したい

**A: ディレクトリごと削除できます。**

```bash
rm -rf .venv uv.lock
```

次回`uv sync`で再作成されます。

### Q: エラーが出たら？

**A: まず`.venv`とロックファイルを削除して再インストール。**

```bash
# クリーンアップ
rm -rf .venv uv.lock

# 再インストール
uv sync
```

---

## pipとの互換性

uvはpipコマンドも提供しています。

```bash
# pip install の代わり
uv pip install <package>

# pip list の代わり
uv pip list

# pip freeze の代わり
uv pip freeze

# requirements.txtの生成
uv pip freeze > requirements.txt
```

ただし、**プロジェクト管理には`uv sync`の使用を推奨**します。

---

## トラブルシューティング

### エラー: `uv: command not found`

**解決方法**: uvをインストールしてください。

```bash
curl -LsSf https://astral.sh/uv/install.sh | sh
```

インストール後、シェルを再起動するか：

```bash
source ~/.bashrc  # または ~/.zshrc
```

### エラー: `No module named 'xxx'`

**解決方法**: 依存関係を再インストール。

```bash
uv sync
```

### エラー: `Failed to create virtualenv`

**解決方法**: Python 3.8以上がインストールされているか確認。

```bash
python3 --version
```

### uvが遅い

**解決方法**: キャッシュをクリア。

```bash
uv cache clean
```

---

## より詳しい情報

- 公式ドキュメント: https://docs.astral.sh/uv/
- GitHubリポジトリ: https://github.com/astral-sh/uv
- リリースノート: https://github.com/astral-sh/uv/releases

---

## まとめ

**uvの3つの基本コマンド:**

1. `uv sync` - 依存関係をインストール
2. `uv run script.py` - スクリプトを実行
3. `uv add <package>` - パッケージを追加

これだけ覚えておけば、ほとんどの作業ができます！
