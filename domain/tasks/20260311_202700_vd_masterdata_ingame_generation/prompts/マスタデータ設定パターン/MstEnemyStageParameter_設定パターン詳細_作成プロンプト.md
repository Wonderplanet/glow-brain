# MstEnemyStageParameter 設定パターン詳細ドキュメント 作成プロンプト

## 目的

`MstEnemyStageParameter` の全カラムについて、設定方法・依存関係・パターンを詳細にまとめたドキュメントを作成する。

---

## Step 1: masterdata-explorer でスキーマ・実データを調査

以下のプロンプトでスキルを呼び出す。

```
/masterdata-explorer MstEnemyStageParameter データの設定方法について詳しくまとめたい。
この列をこの設定にしたら、こういう設定をする必要があるといった設定パターンを漏れなく正確にまとめてほしい。
各列ごとに、正確に詳細に、どんな設定をすれば良いのかを全て把握したい。

まとめたら保存先：specs/*.md
```

### スキルが調査する内容

- DBスキーマ（`mst_enemy_stage_parameters` テーブル）・CSVテンプレート
- character_unit_kind / role_type / color / transformationConditionType の enum 値
- VD既存データ（`vd_all/data/MstEnemyStageParameter.csv`）のサンプル確認

---

## Step 2: C#実装で確認すべきポイント

スキーマや既存ドキュメントの説明が実装と一致しているか確認する。
特に `character_unit_kind` の各種 Boss 系 enum の扱いや、
変身（Transformation）発動条件の詳細は、クライアント実装を確認しないと正確にわからない。

### 調査対象ファイル

| ファイル | 確認内容 |
|---------|---------|
| `CharacterUnitFactory.cs` | MstEnemyStageParameter の各パラメータがどのようにユニットモデルに反映されるか |
| IsBoss 判定ロジック | character_unit_kind が Boss / AdventBattleBoss の場合の特別扱い |

### 確認のポイント

**`e_` プレフィックスと `c_` プレフィックスの違いは何か？**

- `e_`: 敵ユニット（enemy）
- `c_`: フレンドユニット（chara）として扱われるキャラ
- 内部実装でどう区別されているかを確認する

**`attack_combo_cycle=0` のユニットはどう動作するか？**

- コンボ攻撃なし（単発攻撃のみ）か、特殊扱いか

**変身（Transformation）発動条件の詳細**

- transformationConditionType の各 enum 値と、対応する transformation_condition_value の意味
- 変身後のパラメータはどのカラムで指定するか

---

## Step 3: 設定パターン詳細ドキュメントの内容

`specs/MstEnemyStageParameter_設定パターン詳細.md` に以下の内容をまとめる。

1. **概要**: MstEnemyStageParameter の役割と MstAutoPlayerSequence との連携
2. **全カラム一覧**: 型・NULL・VD固定値
3. **character_unit_kind / role_type / color の全 enum 値**: 各値の意味と使い分け
4. **VD固有の ID パターン**: `e_{作品ID}_{キャラID}_vd_{ユニット種別}_{色}` の命名規則
5. **`e_` と `c_` プレフィックスの使い分け**: 敵ユニット vs フレンドユニット
6. **HP パラメータと倍率の関係**: 最終 HP 計算式と VD での設定例
7. **変身機能の設定パターン**: transformationConditionType 別の設定と condition_value の意味
8. **ability との連携**: ability_id の設定方法
9. **vd_all/data/MstEnemyStageParameter.csv の活用方法**: 既存データからの参照手順
10. **注意事項まとめ**: よくある落とし穴（`e_` / `c_` 混在・変身条件の誤設定など）

---

## 関連ドキュメント

- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` — テーブル基本解説
- `vd_all/data/MstEnemyStageParameter.csv` — 全VD作品の既存敵パラメータ一覧
- `specs/MstEnemyStageParameter_設定パターン詳細.md` — 本プロンプトの成果物
