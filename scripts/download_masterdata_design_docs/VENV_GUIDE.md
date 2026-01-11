# Python仮想環境（venv）使い方ガイド

## 仮想環境とは？

Pythonの仮想環境は、プロジェクトごとに独立したPython環境を作成する機能です。

### メリット

- ✅ システムのPython環境を汚さない
- ✅ プロジェクトごとに異なるバージョンのライブラリを使える
- ✅ 依存関係の衝突を防ぐ
- ✅ 再現性の高い環境を作れる

### 例：なぜ仮想環境が必要？

```
システム全体にインストールした場合:
プロジェクトA: requests==2.31.0 が必要
プロジェクトB: requests==2.25.0 が必要
→ 衝突！

仮想環境を使う場合:
プロジェクトA: venvA環境で requests==2.31.0
プロジェクトB: venvB環境で requests==2.25.0
→ 問題なし！
```

## 基本的な使い方

### 1. 仮想環境の作成

```bash
# プロジェクトディレクトリに移動
cd scripts/download_masterdata_design_docs

# 仮想環境を作成（venvという名前のディレクトリができる）
python3 -m venv venv
```

**実行後:**
```
scripts/download_masterdata_design_docs/
├── venv/  # ← 新しく作成される
│   ├── bin/
│   ├── include/
│   ├── lib/
│   └── pyvenv.cfg
├── download_masterdata_design_docs.py
└── ...
```

### 2. 仮想環境の有効化

#### macOS / Linux

```bash
source venv/bin/activate
```

#### Windows

```cmd
venv\Scripts\activate
```

**成功すると:**
プロンプトに `(venv)` が表示されます。

```bash
# 有効化前
user@host:~/scripts/download_masterdata_design_docs$

# 有効化後
(venv) user@host:~/scripts/download_masterdata_design_docs$
```

### 3. ライブラリのインストール

仮想環境を有効化した状態で `pip install` を実行します。

```bash
# 仮想環境内でインストール
pip install -r requirements.txt
```

**確認:**
```bash
# インストールされているパッケージを確認
pip list

# 仮想環境内のPythonを使用していることを確認
which python3
# → /path/to/venv/bin/python3 と表示される
```

### 4. スクリプトの実行

仮想環境を有効化した状態で実行します。

```bash
python3 download_masterdata_design_docs_oauth.py "URL"
```

### 5. 仮想環境の無効化

作業が終わったら、仮想環境を無効化できます。

```bash
deactivate
```

**実行後:**
プロンプトから `(venv)` が消えます。

```bash
# 無効化前
(venv) user@host:~/scripts/download_masterdata_design_docs$

# 無効化後
user@host:~/scripts/download_masterdata_design_docs$
```

## 日常的な使い方

### 毎回のワークフロー

```bash
# 1. プロジェクトディレクトリに移動
cd scripts/download_masterdata_design_docs

# 2. 仮想環境を有効化
source venv/bin/activate

# 3. スクリプトを実行
python3 download_masterdata_design_docs_oauth.py "URL"

# 4. 作業が終わったら無効化（任意）
deactivate
```

## よくある質問

### Q: 仮想環境を毎回作る必要がある？

**A:** いいえ。仮想環境は**一度作成すればOK**です。2回目以降は**有効化するだけ**です。

```bash
# 初回のみ
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# 2回目以降
source venv/bin/activate  # 有効化だけでOK
```

---

### Q: 仮想環境を有効化し忘れたら？

**A:** システム全体にパッケージがインストールされてしまいます。

```bash
# 間違った例（仮想環境未有効化）
pip install -r requirements.txt
# → システム全体にインストールされる

# 正しい例
source venv/bin/activate
pip install -r requirements.txt
# → 仮想環境内にインストールされる
```

**確認方法:**
```bash
which python3
# 仮想環境内 → /path/to/venv/bin/python3
# システム   → /usr/bin/python3 または /usr/local/bin/python3
```

---

### Q: 仮想環境を削除したい

**A:** `venv` ディレクトリを削除するだけです。

```bash
# macOS / Linux
rm -rf venv

# Windows
rmdir /s venv
```

再度作成する場合:
```bash
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

---

### Q: venvディレクトリをGitにコミットすべき？

**A:** いいえ。`.gitignore` に `venv/` を追加して除外します（既に追加済み）。

理由:
- 環境依存のファイルが含まれる
- サイズが大きい
- `requirements.txt` があれば誰でも再現できる

---

### Q: 複数のプロジェクトで仮想環境を共有できる？

**A:** できますが、推奨しません。プロジェクトごとに独立した仮想環境を作成してください。

```
プロジェクトA/
├── venvA/  # プロジェクトA専用
└── ...

プロジェクトB/
├── venvB/  # プロジェクトB専用
└── ...
```

---

### Q: 仮想環境の名前は `venv` でなければダメ？

**A:** いいえ。任意の名前を使えます。

```bash
# 別の名前で作成
python3 -m venv my_env
source my_env/bin/activate

# 一般的な名前
python3 -m venv .venv  # ドット付き（隠しディレクトリ）
python3 -m venv env
python3 -m venv virtualenv
```

ただし、`venv` が最も一般的です。

---

### Q: Pythonのバージョンを指定できる？

**A:** はい。使用するPythonのバージョンを指定できます。

```bash
# Python 3.9で仮想環境を作成
python3.9 -m venv venv

# Python 3.11で仮想環境を作成
python3.11 -m venv venv
```

---

## トラブルシューティング

### エラー: venv モジュールが見つからない

```
No module named venv
```

**解決方法:**

```bash
# Ubuntu/Debian
sudo apt install python3-venv

# macOS（Homebrewでインストールしたpython）
# 通常は不要。python3がインストールされていれば利用可能
```

---

### エラー: activate スクリプトが実行できない

```
bash: venv/bin/activate: No such file or directory
```

**原因:** 仮想環境が作成されていない、またはパスが間違っている。

**解決方法:**
```bash
# 仮想環境を作成
python3 -m venv venv

# パスを確認
ls -la venv/bin/activate
```

---

### エラー: permission denied

```
-bash: venv/bin/activate: Permission denied
```

**解決方法:**
```bash
chmod +x venv/bin/activate
source venv/bin/activate
```

---

## まとめ

### 初回セットアップ

```bash
cd scripts/download_masterdata_design_docs
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### 日常的な使用

```bash
cd scripts/download_masterdata_design_docs
source venv/bin/activate
python3 download_masterdata_design_docs_oauth.py "URL"
deactivate
```

### 覚えておくこと

- ✅ 仮想環境は**一度作成すればOK**
- ✅ 使う前に**必ず有効化**
- ✅ `pip install` は**有効化後に実行**
- ✅ 終わったら**無効化**（任意）
- ✅ `venv/` は**Gitに含めない**

---

これで仮想環境を安全に使えます！
