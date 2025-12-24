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

## 記載形式

### マスタデータセクション

| テーブル名 | カラム名 | 説明 |
|-----------|---------|------|
| **OprGacha**<br>ガチャ基本情報 | GachaId | ガチャID |
| | GachaName | ガチャ名 |
| | GachaType | ガチャタイプ（フェス、ピックアップ、無料など） |
| | EndAt | 終了日時 |

### ユーザーデータセクション

| テーブル名 | カラム名 | 説明 |
|-----------|---------|------|
| **UserGacha**<br>ユーザーガチャ実行履歴 | OprGachaId | ガチャID |
| | GachaExpireAt | 個別有効期限（一部ガチャで設定） |

## 記載ポイント

1. **DBテーブル名ベースで記載**: `OprGacha`（○）、`OprGachaData`や`OprGachaModel`（×）
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
