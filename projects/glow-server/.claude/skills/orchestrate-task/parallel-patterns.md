# 並列化パターン

## 基本原則

依存関係のないTODOは同一グループにまとめ、並列実行する。

## 並列化の判断基準

### 並列実行可能
- ファイル間の依存がない
- 同じリソースへの書き込みがない
- 実行順序が結果に影響しない

### 順次実行必須
- 前のTODOの出力が次の入力になる
- DBマイグレーション → Entity作成 のような依存
- テスト実行 → エラー修正 のような依存

## API実装の標準並列化パターン

```
グループ1: スキーマ確認
  └─ 1. glow-schema確認

グループ2: DB準備
  └─ 2. マイグレーション作成
  └─ 3. マイグレーション実行

グループ3: Entity/Model（並列可能）
  ├─ 4. XxxEntity 作成
  └─ 5. XxxModel 作成

グループ4: Repository（並列可能）
  ├─ 6. XxxRepository インターフェース作成
  ├─ 7. XxxRepositoryImpl クラス作成
  ├─ 8. save() メソッド実装
  └─ 9. find() メソッド実装

グループ5: Service（並列可能）
  ├─ 10. XxxService クラス作成
  ├─ 11. execute() メソッド実装
  ├─ 12. validate() メソッド実装
  └─ 13. calculate() メソッド実装

グループ6: Controller/Response（並列可能）
  ├─ 14. XxxController クラス作成
  ├─ 15. action() メソッド実装
  ├─ 16. XxxResultData クラス作成
  ├─ 17. ResponseFactory メソッド追加
  └─ 18. ルーティング追加

グループ7: テスト（並列可能）
  ├─ 19. XxxServiceTest クラス作成
  ├─ 20. test_success() 実装
  ├─ 21. test_error_case() 実装
  ├─ 22. XxxControllerTest クラス作成
  ├─ 23. test_endpoint_success() 実装
  └─ 24. test_endpoint_error() 実装

グループ8: 品質チェック（順次推奨）
  ├─ 25. phpcs 実行・修正
  ├─ 26. phpstan 実行・修正
  └─ 27. deptrac 実行・修正
```

## 並列実行の呼び出し方

### Taskツールで並列実行

同一グループ内のTaskは**単一メッセージで複数呼び出し**：

```
// グループ4を並列実行
Task(subagent_type="general-purpose", prompt="XxxRepository インターフェース作成...")
Task(subagent_type="general-purpose", prompt="XxxRepositoryImpl クラス作成...")
Task(subagent_type="general-purpose", prompt="save() メソッド実装...")
Task(subagent_type="general-purpose", prompt="find() メソッド実装...")
```

### Skillは順次実行

Skillは基本的に順次実行（Skillが内部で並列化を管理）：

```
Skill("domain-layer")  // Entity/Model/Repository/Service を一括で
Skill("api-endpoint-implementation")  // Controller/Response を一括で
```

## 注意事項

- 並列実行はコンテキストサイズに注意
- 同一ファイルへの同時書き込みは避ける
- エラー発生時は該当グループを再実行
