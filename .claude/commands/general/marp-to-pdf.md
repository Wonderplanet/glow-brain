---
description: marpでmdをPDFに変換し見やすさを確認・改善
argument-hint: <md-file-path>
allowed-tools: Bash(marp:*), Bash(mkdir:*), Bash(cp:*), Bash(ls:*), Bash(npx:*)
---

# Marp PDF変換・最適化

指定されたMarkdownファイルをmarpコマンドでPDFに変換し、レイアウト崩れや見切れがないか確認します。問題がある場合はmdファイルを調整して再変換を繰り返し、見やすいPDFに仕上げます。

## 引数

- `<md-file-path>`: 変換対象のMarkdownファイルのパス（例: `/path/to/slides.md`）

## 制約

- **元のmdファイルは直接編集しない**
- 元ファイルと同じディレクトリに `marp-output/` サブフォルダを作成し、そこにコピーして作業する

## タスク

### 1. 準備

1. 指定された `<md-file-path>` が存在することを確認する
2. 元ファイルのディレクトリパスとファイル名を特定する
3. サブフォルダ `<元ディレクトリ>/marp-output/` を作成する（すでに存在してもOK）
4. mdファイルを `marp-output/` にコピーする（ファイル名はそのまま）

```bash
mkdir -p <元ディレクトリ>/marp-output
cp <md-file-path> <元ディレクトリ>/marp-output/
```

5. コピー先ファイルの先頭にMarpフロントマター（`marp: true` で始まるYAMLブロック）が存在しない場合は、以下を先頭に付加する：

```yaml
---
marp: true
theme: default
paginate: true
style: |
  section {
    font-size: 14px;
    padding: 30px 40px;
    justify-content: flex-start;
  }
  h1 { font-size: 22px; }
  h2 { font-size: 18px; }
  h3 { font-size: 16px; }
  table {
    font-size: 10px;
    width: 100%;
    table-layout: fixed;
  }
  th, td {
    padding: 2px 4px;
    word-break: break-all;
  }
  code { font-size: 10px; }
  blockquote { font-size: 12px; }
  pre { font-size: 10px; }
  img {
    max-width: 88%;
    max-height: 480px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
  }
---
```

### 2. PDF変換

コピーしたmdファイルをmarpでPDFに変換する：

```bash
marp <元ディレクトリ>/marp-output/<ファイル名>.md --pdf --html --allow-local-files --output <元ディレクトリ>/marp-output/<ファイル名>.pdf
```

変換が成功したか確認する（エラーがないか）。marpが未インストールの場合は `npm install -g @marp-team/marp-cli` を案内して処理を停止する。

### 3. PDF品質確認

Readツールを使って生成されたPDFを視覚的に確認し、以下の観点でチェックする：

**確認項目**:
- [ ] テキストが見切れていないか（右端・下端）
- [ ] レイアウト崩れがないか（要素の重なり・ずれ）
- [ ] フォントサイズは適切か（小さすぎて読めない部分がないか）
- [ ] 画像・図表が正常に表示されているか
- [ ] スライドの区切りが適切か
- [ ] 全体的に人が見て内容が伝わるか
- [ ] Mermaidコードブロックがダイアグラム画像として表示されているか（生テキスト表示になっていないか）
- [ ] コンテンツが上端から配置されているか（上部の余白が大きすぎないか）
- [ ] テーブルの列が右にはみ出ていないか
- [ ] コンテンツがスライド枠の端ぎりぎりに詰まっていないか（上下左右に適切なpaddingが確保されているか）
  - 判定目安: テキスト・表の最終行がスライド下端から5%以内に位置している、またはスライド枠に沿っているように見える場合は「詰まりすぎ」と判断する

### 4. 問題がある場合: 修正と再変換

問題が見つかった場合、`marp-output/` 内のmdファイルのみを修正して再変換する。元の `<md-file-path>` は絶対に編集しないこと。

**典型的な修正方法**:

| 問題 | 修正方法 |
|------|---------|
| テキスト見切れ | `font-size` を小さくする、コンテンツを分割して複数スライドにする |
| レイアウト崩れ | マージン・パディング調整、marpディレクティブ（`paginate: true` など）を確認 |
| 文字が小さすぎる | フォントサイズを大きくする、コンテンツ量を減らす |
| スライド分割が変 | `---` の位置を調整する |
| 余白が足りない | `style` ディレクティブでパディングを調整する |
| Mermaidが生コード表示 | `npx @mermaid-js/mermaid-cli` でPNG変換して差し替え（手順は下記「Mermaid対処法」を参照） |
| コンテンツが下方に寄る | `section { justify-content: flex-start; }` をCSSに追加 |
| テーブルが横にはみ出す | `table { table-layout: fixed; } td { word-break: break-all; }` をCSSに追加 |
| コンテンツが端ぎりぎりに詰まっている | ①`font-size`をさらに1〜2px縮小 ②該当スライドを`---`で2分割 ③`section { padding }` を広げる（例: `40px 60px`） — いずれかまたは組み合わせで対処 |

修正後、ステップ2（PDF変換）→ ステップ3（品質確認）を繰り返す。問題がなくなるまで繰り返す。

### 5. 完了報告

問題なく見やすいPDFになったら完了を宣言し、以下を報告する：

- 最終PDFのパス（`marp-output/` 内）
- 実施した修正の概要（変更がある場合）
- 修正なしで完了した場合はその旨

## Mermaid対処法

Marpでは `mermaid` コードブロックが環境によってレンダリングされない場合がある。その場合は以下の手順でPNG画像に変換して差し替える：

```bash
# 1. Mermaidコードを .mmd ファイルに保存（marp-output/ 配下に作成する）

# 2. mmdc (npx経由) でPNG変換
npx @mermaid-js/mermaid-cli \
  -i <marp-output>/<ファイル名>.mmd \
  -o <marp-output>/flowchart.png \
  --scale 2 -w 900

# 3. mdファイル内のmermaidブロックを画像参照に置き換え
# ```mermaid ... ``` → ![説明](flowchart.png)
```

**レイアウト選択の指針**:
- ノード数が4以下またはLRで幅1200px以内 → `flowchart LR`（横型）
- ノード数が5以上またはLRで幅が広すぎる → `flowchart TB`（縦型）を推奨

## 注意事項

- **元のmdファイルは変更禁止**。必ず `marp-output/` 内のコピーのみを編集すること
- marpのディレクティブ（`<!-- marp: true -->`、`theme:`、`backgroundColor:` など）が既存ファイルにある場合は尊重する
- 修正はレイアウト・見た目の調整に留め、コンテンツの意味・内容を変えないようにする
- 繰り返し修正する際は、各ラウンドで何を修正したかを追跡する

## 出力例

```
✅ PDF変換完了

📄 出力先: /path/to/marp-output/slides.pdf

🔧 実施した修正:
  - スライド3: フォントサイズを24px → 18px に縮小（テキスト見切れ対応）
  - スライド5: コンテンツを2スライドに分割（情報過多）
  - 全体: padding を 30px → 50px に拡大（余白改善）
  - Mermaidブロック: PNG変換してimgタグで差し替え（生コード表示対応）
```
