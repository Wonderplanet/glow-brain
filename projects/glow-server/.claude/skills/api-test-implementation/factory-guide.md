# Factoryガイド

## 基本操作

```php
// DB保存
UsrUser::factory()->create(['id' => 'user123']);

// インスタンスのみ（DB保存しない）
UsrUser::factory()->make(['id' => 'user123']);

// 複数レコード
MstItem::factory()->createMany([
    ['id' => 'item1', 'type' => ItemType::COIN],
    ['id' => 'item2', 'type' => ItemType::STAMINA],
]);

// 同じ属性で複数
UsrItem::factory()->count(5)->create(['usr_user_id' => $usrUserId]);
```

## Entityへの変換

```php
// toEntity()でEntity取得
$mstItemEntity = MstItem::factory()->create(['id' => 'item1'])->toEntity();

// Serviceでそのまま使用
$this->itemService->apply($usrUserId, $platform, $mstItemEntity, $amount, $now);
```

## 主要データ作成

```php
// マスター
MstItem::factory()->create(['id' => 'item1', 'type' => ItemType::COIN->value]);
MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10]);

// ユーザー作成。APIリクエストを行ったユーザーのデータを作る際に使用
// フレンドやマッチング相手など、他人のデータの場合は、UsrUser::factoryで作成する。
$usrUser = $this->createUsrUser(['tutorial_status' => 0]);

// ユーザーパラメータ
UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1000]);

// ユーザーアイテム
UsrItem::factory()->create(['usr_user_id' => $usrUserId, 'mst_item_id' => 'item1']);
```

## Faker

```php
$clientUuid = fake()->uuid();
$randomString = fake()->word();
$randomInt = fake()->numberBetween(1, 100);
```
