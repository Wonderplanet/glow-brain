# マスタデータ調査ガイド

## マスタデータの特定方法

### 1. UseCaseを確認

Repositoryのインジェクションから使用マスタを特定：

```csharp
public class GachaContentUseCase
{
    [Inject] IOprGachaRepository OprGachaRepository { get; }
    [Inject] IOprGachaUpperRepository OprGachaUpperRepository { get; }
    [Inject] IMstItemDataRepository MstItemDataRepository { get; }
    [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
    // ↑ これらのRepositoryから使用マスタを特定
}
```

### 2. Repository名からDBテーブル名を特定

- `IOprGachaRepository` → `OprGacha` → `opr_gacha`
- `IMstCharacterDataRepository` → `MstCharacter` → `mst_units`
- `IMstItemDataRepository` → `MstItem` → `mst_items`

**注意**: `DataRepository`を除外してテーブル名を特定

### 3. Modelのプロパティからカラムを特定

```csharp
var gachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
// ↓ OprGachaModelのプロパティを確認
// GachaId, GachaName, GachaType, EndAt, Description など
```

### 4. DBスキーマを確認（正確なカラム情報の取得）

`projects/glow-server/api/database/schema/master_tables_ddl.sql`で実際のカラム定義を確認：

```sql
CREATE TABLE `opr_gacha` (
  `id` varchar(255) NOT NULL COMMENT 'UUID',
  `gacha_type` enum(...) NOT NULL COMMENT 'ガチャタイプ',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  ...
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ガチャ設定';
```

**DDLファイルの活用**:
- カラムの正確なデータ型を確認
- COMMENTから日本語の説明を取得
- NOT NULL制約などの制約情報を確認
- テーブル全体のCOMMENTから日本語テーブル名を取得
- サーバー定義のカラム名（スネークケース）を正確に把握

## 記載形式

### マスタデータセクション

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| OprGacha.GachaId | opr_gachas.id | ガチャID |
| OprGacha.GachaName | opr_gachas.gacha_name | ガチャ名 |
| OprGacha.GachaType | opr_gachas.gacha_type | ガチャタイプ（フェス、ピックアップ、無料など） |
| OprGacha.EndAt | opr_gachas.end_at | 終了日時 |

### ユーザーデータセクション

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| UserGacha.OprGachaId | user_gachas.opr_gacha_id | ガチャID |
| UserGacha.GachaExpireAt | user_gachas.gacha_expire_at | 個別有効期限（一部ガチャで設定） |

## 記載ポイント

1. **クライアント定義・サーバー定義の両方を記載**: クライアント側とサーバー側の対応を明確にする
   - クライアント定義: `OprGacha.GachaId` （アッパーキャメルの単数クラス名.プロパティ名）
   - サーバー定義: `opr_gachas.id` （スネークケースの複数形テーブル名.カラム名）
2. **使用されているカラムのみ記載**: テーブルの全カラムではなく、画面で実際に使われているもののみ
3. **データの用途を明記**: 単にカラム名を列挙するだけでなく、何のために使っているかを説明
4. **データ間の関連を説明**: 外部キーの関連を明記
5. **Mst/Oprの区分はしない**: すべて「マスタデータ」セクションにまとめて記載

## DBテーブルとクライアント側の対応

| DBテーブル | Domain層 | Repository |
|-----------|---------|-----------|
| mst_units | MstCharacterModel | IMstCharacterDataRepository |
| mst_items | MstItemModel | IMstItemDataRepository |
| opr_gacha | OprGachaModel | IOprGachaRepository |
| opr_gacha_upper | OprGachaUpperModel | IOprGachaUpperRepository |
