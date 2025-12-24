# GLOWマスタデータ作成ガイド

このディレクトリには、GitHub Copilotを使用してGLOWプロジェクトのマスタデータを生成するためのリソースが含まれています。

## 概要

GitHub Copilotプロンプトファイル（`.github/prompts/generate-masterdata.prompt.md`）を使用することで、要件に基づいたマスタデータを自動生成できます。

## 使い方

### 1. GitHub Copilot Chatを開く

VS Codeで以下のいずれかの方法でCopilot Chatを開きます：
- `Cmd+Shift+I` (Mac) / `Ctrl+Shift+I` (Windows)
- サイドバーのCopilotアイコンをクリック

### 2. プロンプトを実行

Copilot Chatで以下のように入力：

```
/generate-masterdata 新春限定ガチャを追加。期間は2026年1月1日〜1月31日。10連ガチャで1回確定報酬あり。
```

### 3. 生成されたデータを確認

以下のディレクトリに生成されます：

```
docs/マスタデータ作成/生成データ/[要件を要約した日本語]/
├── REPORT.md              # 生成レポート
├── OprGacha.csv          # ガチャマスタデータ
└── （その他の関連ファイル）
```

### 4. 本来のリポジトリに適用

生成されたCSVファイルを本来のリポジトリにコピーして使用：

```bash
# glow-masterdataリポジトリに移動
cd /path/to/glow-masterdata

# 生成されたファイルをコピー
cp /path/to/glow-brain/docs/マスタデータ作成/生成データ/[フォルダ名]/*.csv .

# コミット・プッシュ
git add .
git commit -m "新春限定ガチャのマスタデータを追加"
git push
```

## プロンプトの動作

GitHub Copilotプロンプトは以下の処理を自動で行います：

1. **要件の分析**: ユーザーが指定した要件を解析
2. **既存データ調査**:
   - `projects/glow-masterdata/`の既存CSVファイル
   - `projects/glow-client/`のデータモデル
   - `projects/glow-server/`のデータモデル
3. **データ設計**: 既存データに準拠したスキーマ設計
4. **CSV生成**: 要件を満たすデータをCSV形式で生成
5. **レポート作成**: 生成内容を詳細にドキュメント化

## ディレクトリ構造

```
docs/マスタデータ作成/
├── README.md                    # このファイル
└── 生成データ/
    ├── 新春限定ガチャ/
    │   ├── REPORT.md
    │   └── OprGacha.csv
    ├── イベント第1弾/
    │   ├── REPORT.md
    │   ├── MstAdventBattle.csv
    │   └── MstAdventBattleReward.csv
    └── （その他の生成データ）
```

## マスタデータの種類

### Mst系（静的マスタデータ）
- `MstUnit.csv`: ユニット定義
- `MstAbility.csv`: アビリティ定義
- `MstAdventBattle.csv`: アドベントバトル定義
- `MstAttack.csv`: 攻撃定義
- など

### Opr系（運用系マスタデータ）
- `OprGacha.csv`: ガチャ定義
- `OprCampaign.csv`: キャンペーン定義
- など

### I18n系（多言語対応）
多くのマスタデータには対応する`*I18n.csv`ファイルが存在します：
- `MstUnitI18n.csv`: ユニット名の多言語対応
- `OprGachaI18n.csv`: ガチャ名の多言語対応
- など

## 使用例

### 例1: 新ガチャの追加

```
/generate-masterdata バレンタインガチャを追加。2026年2月1日〜2月14日まで。特別報酬あり。
```

### 例2: 新イベントの追加

```
/generate-masterdata 夏祭りイベントを追加。3ステージ、各ステージ5バトル。報酬はコインとアイテム。
```

### 例3: 新キャラクターの追加

```
/generate-masterdata 新キャラクター「春風太郎」を追加。SSRランク、攻撃タイプ、赤色、中距離攻撃。
```

## CSV形式の基本ルール

### 1行目: カラム定義
```csv
ENABLE,id,column1,column2,...
```

### 2行目以降: データ行
```csv
e,unique_id,value1,value2,...
```

### 特殊な値
- **NULL**: `__NULL__`
- **ENABLE状態**: `e` (有効) / `d` (無効)
- **日時**: `YYYY-MM-DD HH:MM:SS`

## 注意事項

### 参照専用リポジトリ
- **glow-brain**は参照専用です
- `projects/`以下のファイルは編集しないでください
- 生成したデータは必ず`docs/`以下に保存されます

### データ整合性
- 生成されたデータは必ずレビューしてください
- ID重複がないか確認してください
- 外部キー参照が正しいか確認してください

### 本番適用
1. 生成レポート（REPORT.md）を確認
2. CSVファイルの内容を検証
3. 本来のリポジトリにコピー
4. テスト環境で動作確認
5. 問題なければ本番環境へデプロイ

## トラブルシューティング

### プロンプトが見つからない場合

VS Codeで`.github/prompts/`ディレクトリが認識されているか確認：
```bash
ls -la .github/prompts/
```

### 生成データが期待と異なる場合

プロンプトに詳細な要件を追加：
```
/generate-masterdata [詳細な要件]。IDは1000から開始。期間は3ヶ月。報酬はゴールド100個とアイテムA。
```

### 既存データとの整合性エラー

REPORTに記載された参照データを確認し、手動で調整：
```bash
cat projects/glow-masterdata/[ModelName].csv | grep [検索パターン]
```

## 関連ドキュメント

- プロンプトファイル: `.github/prompts/generate-masterdata.prompt.md`
- プロジェクト概要: `CLAUDE.md`
- バージョン設定: `config/versions.json`
- glow-serverドキュメント: `projects/glow-server/CLAUDE.md`
- glow-clientドキュメント: `projects/glow-client/README.md`

## フィードバック

プロンプトの改善提案や不具合報告は、開発チームまでお願いします。
