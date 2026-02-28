# Spreadsheet IaC 完全再現基盤 実装

## 参照ドキュメント

- 仕様書: @.claude/plans/spreadsheet-iac-complete-reproduction-spec.md
- 実装設計書: @domain/tools/spreadsheet-iac/implementation-design.md
- 実装計画書: @domain/tools/spreadsheet-iac/implementation-plan.md

## 現在の実装状況

なし（新規実装）。`domain/tools/spreadsheet-iac/` ディレクトリは存在するが、実装コードはまだない。

参考にすべき既存 Python ツールの構造:
- `domain/tools/clickup/` — pyproject.toml, src/ 構成, dataclass モデルのパターン

## 実装指示

上記の実装設計書・実装計画書に従って、Spreadsheet IaC 完全再現基盤を実装してください。

### 実装対象ディレクトリ

```
domain/tools/spreadsheet-iac/
```

### 実行順序

実装計画書の各 Group を順番に実行してください。
Group 3（Snapshot Engine）、Group 4（Build Engine）、Group 5（Diff Engine）は Group 2 完了後に**並列実行可能**です。

```
Group 1（順次）: pyproject.toml + ディレクトリ構造作成
Group 2（順次）: Pydantic モデル実装（models/）
Group 3, 4, 5（並列可）: 各エンジン実装
Group 6（並列可）: テスト実装
Group 7（順次）: ruff lint/format → pytest 全件実行
```

### 重要な実装上の注意点

1. **Python バージョン**: `>=3.11`（`match-case` 構文、`dict[str, T]` 型ヒント使用可）
2. **Pydantic v2 API**: `model_dump_json()`, `model_validate_json()` を使用（v1 の `dict()`, `parse_raw()` は使わない）
3. **openpyxl 数式取得**: `load_workbook(path, data_only=False)` で開くこと
4. **空セルスキップ**: `value` が `None` かつスタイルがデフォルトのセルは `CellSpec` に含めない
5. **既存ツールとの統一**: `pyproject.toml` の `ruff` 設定は `domain/tools/clickup/pyproject.toml` に準拠

### 完了確認

全 TODO 完了後、以下を実行してエラーがないことを確認してください:

```bash
cd domain/tools/spreadsheet-iac
uv run ruff check src/ tests/
uv run pytest tests/ -v --cov=spreadsheet_iac
```
