# Filament URL構造とルーティング

glow-server adminはFilament v3を使用しており、独自のルーティング構造を持ちます。

## 目次

- [基本URL構造](#基本url構造)
- [Resourceのルーティング](#resourceのルーティング)
- [カスタムPageのルーティング](#カスタムpageのルーティング)
- [URL生成方法](#url生成方法)

## 基本URL構造

### ベースURL

開発環境: `http://localhost:{NGINX_ADMIN_PORT}/admin`

例: `http://localhost:8081/admin`

### URL形式

```
{ベースURL}/{パス}?{クエリパラメータ}
```

## Resourceのルーティング

### Resource定義

`admin/app/Filament/Resources/{ResourceName}.php` で定義:

```php
class UsrUserResource extends Resource
{
    protected static ?string $model = UsrUser::class;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsrUsers::route('/'),
            'view' => Pages\ViewUsrUser::route('{record}'),
        ];
    }
}
```

### URL生成規則

1. **クラス名からslug生成**: `UsrUserResource` → `usr-users`
   - キャメルケースをケバブケースに変換
   - 末尾の `Resource` を除去

2. **ページ別のパス**:
   - `index`: `/admin/{slug}`
   - `view`: `/admin/{slug}/{record}`
   - `create`: `/admin/{slug}/create`
   - `edit`: `/admin/{slug}/{record}/edit`

### 実例

**UsrUserResource (admin/app/Filament/Resources/UsrUserResource.php:18)**

```php
class UsrUserResource extends Resource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsrUsers::route('/'),
            'view' => Pages\ViewUsrUser::route('{record}'),
        ];
    }
}
```

生成されるURL:
- 一覧: `http://localhost:8081/admin/usr-users`
- 詳細: `http://localhost:8081/admin/usr-users/123`

## カスタムPageのルーティング

### Page定義

`admin/app/Filament/Pages/{PageName}.php` で定義:

```php
class EditUserUnit extends UserDataBasePage
{
    protected $queryString = [
        'userId',
        'unitId',
    ];

    public static function getUrl(array $parameters = []): string
    {
        return static::$url . '?' . http_build_query($parameters);
    }
}
```

### slug指定

明示的に `$slug` を定義することでURLをカスタマイズ可能:

```php
class ServerTimeSetting extends Page
{
    protected static ?string $slug = 'server-time-setting';
}
```

→ URL: `/admin/server-time-setting`

### URL生成規則

1. **slug未定義の場合**: クラス名から自動生成
   - `EditUserUnit` → `edit-user-unit`
   - キャメルケースをケバブケースに変換

2. **クエリパラメータ**: `$queryString` プロパティで定義
   - `getUrl()` メソッドで配列として渡す

### 実例

**EditUserUnit (admin/app/Filament/Pages/EditUserUnit.php:17)**

```php
class EditUserUnit extends UserDataBasePage
{
    protected $queryString = [
        'userId',
        'unitId',
    ];
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

**BulkUpdateUserUnitLevel (admin/app/Filament/Pages/BulkUpdateUserUnitLevel.php:20)**

```php
class BulkUpdateUserUnitLevel extends UserDataBasePage
{
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

URL生成:
```php
BulkUpdateUserUnitLevel::getUrl(['userId' => 123])
```

→ `http://localhost:8081/admin/bulk-update-user-unit-level?userId=123`

## URL生成方法

### コード内での生成

```php
// Resource
UsrUserResource::getUrl('view', ['record' => 123])
// → /admin/usr-users/123

// Page
EditUserUnit::getUrl(['userId' => 123, 'unitId' => 'unit_001'])
// → /admin/edit-user-unit?userId=123&unitId=unit_001
```

### 手動での生成

1. **クラス名を確認**: `admin/app/Filament/Resources/` または `admin/app/Filament/Pages/`
2. **slugに変換**: キャメルケース → ケバブケース
3. **パラメータ特定**: `$queryString` プロパティまたは `route()` 定義を確認
4. **URL構築**: `{ベースURL}/{slug}?{パラメータ}`

## チェックリスト

ページ遷移前の確認:
- [ ] 遷移先のPageクラスまたはResourceクラスを特定した
- [ ] URL slugを特定した (自動生成またはカスタム定義)
- [ ] 必要なクエリパラメータを特定した
- [ ] パラメータの値 (userId等) を取得した
- [ ] 完全なURLを構築した
