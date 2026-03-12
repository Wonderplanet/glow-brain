# MstAutoPlayerSequence 設定パターン詳細ドキュメント 作成プロンプト

## 目的

`MstAutoPlayerSequence` の全カラムについて、設定方法・依存関係・パターンを詳細にまとめたドキュメントを作成する。

---

## Step 1: masterdata-explorer でスキーマ・実データを調査

以下のプロンプトでスキルを呼び出す。

```
/masterdata-explorer MstAutoPlayerSequence データの設定方法について詳しくまとめたい。
この列をこの設定にしたら、こういう設定をする必要があるといった設定パターンを漏れなく正確にまとめてほしい。
各列ごとに、正確に詳細に、どんな設定をすれば良いのかを全て把握したい。

まとめたら保存先：specs/*.md
```

### スキルが調査する内容

- DBスキーマ（カラム名・型・NULL可・デフォルト値・コメント）
- CSVテンプレート（列順序）
- Enum定義（C#・glow-schema YAML）
- 既存CSVデータのサンプル（VDブロックの実データ）

---

## Step 2: C#実装でEnum・条件の正確な動作を確認

スキーマや既存ドキュメントの説明が実装と一致しているか確認する。
特に `condition_type` / `action_type` の各enumが実際にどう動作するかは、
クライアント実装を確認しないと正確にわからない。

### 調査対象ファイル

| ファイル | 確認内容 |
|---------|---------|
| `AutoPlayerSequenceElementStateModelFactory.cs` | condition_type → InGameCommonConditionType のマッピング |
| `CommonConditionModelFactory.cs` | InGameCommonConditionType → Model のマッピング・condition_value の変換メソッド |
| `EnemyUnitDeadCommonConditionModel.cs` | FriendUnitDead の実際の判定ロジック |
| `CommonConditionValue.cs` | condition_value の各変換メソッド（ToAutoPlayerSequenceElementId・ToDefeatEnemyCount 等） |

### 確認のポイント

**`FriendUnitDead` の condition_value は何を意味するか？**

- `EnemyUnitDeadCommonConditionModel` → `value.ToAutoPlayerSequenceElementId()` を使用
- `ToAutoPlayerSequenceElementId()` → `new AutoPlayerSequenceElementId(Value)` で文字列をそのまま渡す（int.Parseしない）
- 判定ロジック: `context.DeadUnits.Any(unit => unit.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId)`

→ **累計撃破数ではなく、参照先の sequence_element_id（文字列）**

> **注意**: 自分自身の sequence_element_id を condition_value に書くと循環参照になり永遠に発火しない。

---

## Step 3: 設定パターン詳細ドキュメントの内容

`specs/MstAutoPlayerSequence_設定パターン詳細.md` に以下の内容をまとめる。

1. **概要**: 3層構造（sequence_set_id → sequence_group_id → sequence_element_id）
2. **全カラム一覧**: 型・NULL・デフォルト・VD固定値
3. **condition_type 別の設定パターン**: 全11種類の条件と condition_value の意味
4. **action_type 別の設定パターン**: 全8種類のアクションと必須カラム
5. **move系カラム**: move_start/stop/restart の全パターン
6. **aura_type / death_type**: VDでの選択基準（c_→Boss / e_→Default）
7. **enemy_*_coef**: 最終パラメータ計算式とVDでの設定例
8. **VD固有パターン**: VD固定値一覧・Normal/Bossブロックの典型構成
9. **IDの命名規則**: id / action_value の命名ルール
10. **注意事項まとめ**: よくある落とし穴
11. **action_typeと必須カラムの依存関係早見表**

---

## 関連ドキュメント

- `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` — テーブル基本解説（本プロンプトで修正済み）
- `specs/MstAutoPlayerSequence_設定パターン詳細.md` — 本プロンプトの成果物
