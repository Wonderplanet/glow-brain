# カスタムページへの遷移例

Filament カスタムPage (ユーザーデータ編集、検索など) への遷移方法を実例で説明します。

## 基本パターン

### クエリパラメータ付きPage

**EditUserUnit (admin/app/Filament/Pages/EditUserUnit.php:17)**

```php
class EditUserUnit extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.common.update-form-page';

    protected $queryString = [
        'userId',
        'unitId',
    ];

    public function mount()
    {
        parent::mount();
        // userId, unitId がクエリパラメータから自動バインド
    }
}
```

URL生成:
```php
EditUserUnit::getUrl([
    'userId' => 123,
    'unitId' => 'unit_001',
])
```

→ `http://localhost:8081/admin/edit-user-unit?userId=123&unitId=unit_001`

## ユーザーデータ編集ページ

### UserUnit系

**UserUnit一覧 (admin/app/Filament/Pages/UserUnit.php:20)**

```php
class UserUnit extends UserDataBasePage implements Tables\Contracts\HasTable
{
    public string $currentTab = UserSearchTabs::UNIT->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }
}
```

URL: `http://localhost:8081/admin/user-unit?userId=123`

パラメータ:
- `userId`: usr_user.id

**EditUserUnit編集 (admin/app/Filament/Pages/EditUserUnit.php)**

URL: `http://localhost:8081/admin/edit-user-unit?userId=123&unitId=unit_001`

パラメータ:
- `userId`: usr_user.id
- `unitId`: mst_unit.id

**BulkUpdateUserUnitLevel一括レベル更新 (admin/app/Filament/Pages/BulkUpdateUserUnitLevel.php:20)**

```php
class BulkUpdateUserUnitLevel extends UserDataBasePage implements HasForms
{
    protected static string $view = 'filament.pages.bulk-update-user-unit-level';
    public string $currentTab = UserSearchTabs::UNIT->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserUnit::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId]) => '一括レベル更新',
        ]);
    }
}
```

URL: `http://localhost:8081/admin/bulk-update-user-unit-level?userId=123`

パラメータ:
- `userId`: usr_user.id

### その他ユーザーデータ編集

**EditUserParameter (admin/app/Filament/Pages/EditUserParameter.php)**

URL: `http://localhost:8081/admin/edit-user-parameter?userId=123`

**EditUserItem (admin/app/Filament/Pages/EditUserItem.php)**

URL: `http://localhost:8081/admin/edit-user-item?userId=123`

**EditUserGacha (admin/app/Filament/Pages/EditUserGacha.php)**

URL: `http://localhost:8081/admin/edit-user-gacha?userId=123`

## マスターデータ詳細ページ

### MstUnitDetail

**MstUnitDetail (admin/app/Filament/Pages/MstUnitDetail.php)**

UserUnit.phpからの参照例:
```php
MstIdColumn::make('mst_unit_info')
    ->label('キャラ情報')
    ->getMstUsing(function (UsrUnit $model) {
        return $model->mst_unit;
    })
    ->getMstDetailPageUrlUsing(function (UsrUnit $model) {
        return MstUnitDetail::getUrl(['mstUnitId' => $model->mst_unit_id]);
    }),
```

URL: `http://localhost:8081/admin/mst-unit-detail?mstUnitId=unit_001`

パラメータ:
- `mstUnitId`: mst_unit.id

### その他マスターデータ詳細

**MstItemDetail**

URL: `http://localhost:8081/admin/mst-item-detail?mstItemId=item_001`

**MstEmblemDetail (admin/app/Filament/Pages/EmblemDetail.php)**

slug定義がある場合も確認:
```php
class EmblemDetail extends Page
{
    // slug未定義の場合: クラス名から自動生成
}
```

URL: `http://localhost:8081/admin/emblem-detail?mstEmblemId=emblem_001`

## カスタムslug定義ページ

### slug明示定義の例

**ServerTimeSetting (admin/app/Filament/Pages/ServerTimeSetting.php:27)**

```php
class ServerTimeSetting extends Page
{
    protected static ?string $slug = 'server-time-setting';
}
```

URL: `http://localhost:8081/admin/server-time-setting`

**Import (admin/app/Filament/Pages/Import.php:15)**

```php
class Import extends Page
{
    protected static ?string $slug = 'import';
}
```

URL: `http://localhost:8081/admin/import`

**MasterDataDiff (admin/app/Filament/Pages/MasterDataDiff.php:16)**

```php
class MasterDataDiff extends Page
{
    protected static ?string $slug = 'master-data-diff';
}
```

URL: `http://localhost:8081/admin/master-data-diff`

## パラメータ特定方法

### ステップ1: Pageクラスを探す

```bash
# カスタムPageファイル検索
ls admin/app/Filament/Pages/*.php

# クラス名で検索
grep -r "class.*extends.*Page" admin/app/Filament/Pages/
```

### ステップ2: $queryStringを確認

```php
protected $queryString = [
    'userId',
    'unitId',
    // ...
];
```

これらがクエリパラメータとして必要。

### ステップ3: mount()やgetUrl()の使用例を確認

他のページから遷移する際の実例:

```php
// UserUnit.php:101
Action::make('edit')
    ->url(function (UsrUnit $record) {
        return EditUserUnit::getUrl([
            'userId' => $this->userId,
            'unitId' => $record->mst_unit_id,
        ]);
    }),
```

→ `userId` と `unitId` が必要と分かる

### ステップ4: パラメータ値を特定

- `userId`: usr_user.id → DBから適切なユーザーを検索
- `unitId`: mst_unit.id → マスターデータから取得
- `mstUnitId`: mst_unit.id → 同上

## 複雑なパラメータ構造の例

### MstComebackBonusDetail (admin/app/Filament/Pages/MstComebackBonusDetail.php:24)**

```php
class MstComebackBonusDetail extends MstDetailBasePage
{
    protected static ?string $slug = 'mst-comeback-bonus-detail/{mstComebackBonusScheduleId}';

    public ?string $mstComebackBonusScheduleId = '';

    protected $queryString = [
        'mstComebackBonusScheduleId',
    ];
}
```

URL: `http://localhost:8081/admin/mst-comeback-bonus-detail/schedule_001?mstComebackBonusScheduleId=schedule_001`

パラメータ:
- パス: `{mstComebackBonusScheduleId}`
- クエリ: `mstComebackBonusScheduleId`

## UserDataBasePage共通パターン

UserDataBasePageを継承するページは共通してuserIdを使用:

```php
abstract class UserDataBasePage extends Page
{
    public string $userId = '';

    protected $queryString = [
        'userId',
    ];
}
```

継承ページ例:
- EditUserUnit
- EditUserParameter
- EditUserItem
- EditUserGacha
- UserUnit
- BulkUpdateUserUnitLevel

全て `?userId={usr_user.id}` パラメータが必要。

## チェックリスト

カスタムページ遷移前:
- [ ] 対象Pageクラスを特定した
- [ ] slug名を特定した (自動生成またはカスタム定義)
- [ ] $queryStringで定義されたパラメータを確認した
- [ ] 各パラメータの値 (ID等) を取得した
- [ ] URLを構築した
- [ ] 必要に応じて他ページからの遷移コードを参考にした
