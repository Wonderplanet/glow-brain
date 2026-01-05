# Resourceページへの遷移例

Filament Resourceページ (一覧、詳細、編集など) への遷移方法を実例で説明します。

## 基本パターン

### 一覧ページ (index)

**UsrUserResource一覧**

ファイル: `admin/app/Filament/Resources/UsrUserResource.php`

```php
class UsrUserResource extends Resource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsrUsers::route('/'),
            // ...
        ];
    }
}
```

URL: `http://localhost:8081/admin/usr-users`

### 詳細ページ (view)

**UsrUser詳細**

```php
public static function getPages(): array
{
    return [
        'view' => Pages\ViewUsrUser::route('{record}'),
    ];
}
```

URL: `http://localhost:8081/admin/usr-users/{id}`

例: `http://localhost:8081/admin/usr-users/123`

パラメータ:
- `{record}`: レコードのID (通常はプライマリキー)

## 実例集

### マスターデータ

**MstUnitResource (admin/app/Filament/Resources/MstUnitResource.php)**

```php
class MstUnitResource extends Resource
{
    protected static ?string $model = MstUnit::class;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstUnits::route('/'),
        ];
    }
}
```

- 一覧: `http://localhost:8081/admin/mst-units`

**MstEventResource (admin/app/Filament/Resources/MstEventResource.php)**

```php
class MstEventResource extends Resource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMstEvents::route('/'),
        ];
    }
}
```

- 一覧: `http://localhost:8081/admin/mst-events`

### 運用データ

**OprGachaResource (admin/app/Filament/Resources/OprGachaResource.php)**

- 一覧: `http://localhost:8081/admin/opr-gachas`

**OprProductResource (admin/app/Filament/Resources/OprProductResource.php)**

- 一覧: `http://localhost:8081/admin/opr-products`

### 管理データ

**AdmUserResource (admin/app/Filament/Resources/AdmUserResource.php)**

- 一覧: `http://localhost:8081/admin/adm-users`

## CRUD操作付きResource

### 作成・編集が可能なResource

**MngJumpPlusRewardScheduleResource (admin/app/Filament/Resources/MngJumpPlusRewardScheduleResource.php)**

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListMngJumpPlusRewardSchedules::route('/'),
        'create' => Pages\CreateMngJumpPlusRewardSchedule::route('/create'),
        'edit' => Pages\EditMngJumpPlusRewardSchedule::route('/{record}/edit'),
    ];
}
```

URL:
- 一覧: `http://localhost:8081/admin/mng-jump-plus-reward-schedules`
- 新規作成: `http://localhost:8081/admin/mng-jump-plus-reward-schedules/create`
- 編集: `http://localhost:8081/admin/mng-jump-plus-reward-schedules/123/edit`

## URL特定の手順

### ステップ1: Resourceクラスを探す

```bash
# Resourceファイル検索
ls admin/app/Filament/Resources/*Resource.php

# クラス名で検索
grep -r "class.*Resource extends Resource" admin/app/Filament/Resources/
```

### ステップ2: slug生成規則を適用

クラス名 → slug変換:
1. 末尾の `Resource` を除去
2. キャメルケース → ケバブケース

例:
- `UsrUserResource` → `usr-user` → `usr-users` (複数形化)
- `MstUnitResource` → `mst-unit` → `mst-units`
- `OprGachaResource` → `opr-gacha` → `opr-gachas`

### ステップ3: getPages()を確認

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListXxx::route('/'),
        'view' => Pages\ViewXxx::route('{record}'),
        // ...
    ];
}
```

- `index`: 一覧ページ
- `view`: 詳細ページ
- `create`: 新規作成ページ
- `edit`: 編集ページ

### ステップ4: URL構築

```
{ベースURL}/{slug}[/{record}][/アクション]
```

## よくあるパターン

### 一覧のみ提供

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListXxx::route('/'),
    ];
}
```

→ 一覧ページのみアクセス可能

### 一覧+詳細

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListXxx::route('/'),
        'view' => Pages\ViewXxx::route('{record}'),
    ];
}
```

→ 一覧と詳細ページがアクセス可能

### フルCRUD

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListXxx::route('/'),
        'create' => Pages\CreateXxx::route('/create'),
        'view' => Pages\ViewXxx::route('{record}'),
        'edit' => Pages\EditXxx::route('/{record}/edit'),
    ];
}
```

→ 全てのCRUD操作がアクセス可能

## チェックリスト

Resourceページ遷移前:
- [ ] 対象Resourceクラスを特定した
- [ ] slug名を特定した
- [ ] アクセスしたいページ種別(index/view/edit等)を確認した
- [ ] 必要なパラメータ({record}等)を確認した
- [ ] URLを構築した
