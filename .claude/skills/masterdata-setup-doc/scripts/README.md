# マスタデータ設定方法ドキュメント生成スクリプト

## 概要

2つのスクリプトで構成：

1. **analyze_content.py**: コンテンツのスキーマを分析してJSON出力
2. **generate_doc.py**: 分析結果からMarkdownドキュメント生成

## 使用方法

### 基本

```bash
python scripts/analyze_content.py "降臨バトル" --output /tmp/analysis.json
python scripts/generate_doc.py /tmp/analysis.json "降臨バトル" --output マスタデータ/設定方法/降臨バトル.md
```

### ワンライナー

```bash
python scripts/analyze_content.py "降臨バトル" | \
python scripts/generate_doc.py /dev/stdin "降臨バトル" --output マスタデータ/設定方法/降臨バトル.md
```

## 詳細

各スクリプトの仕様とカスタマイズ方法はコード内のdocstringを参照してください。
