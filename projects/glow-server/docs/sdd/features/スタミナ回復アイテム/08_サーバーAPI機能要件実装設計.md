# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- **対象機能**: スタミナ回復アイテム
- **作成日**: 2025-11-27
- **参照ドキュメント**:
  - 05_サーバーAPI要件書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針

本機能は、既存のアイテム管理システムとスタミナ管理システムを統合する形で実装します。

**基本方針:**
- **既存実装の最大限の活用**: 既存のItemService、UserBuyStaminaService、UsrItemServiceの実装パターンを踏襲
- **新規コンポーネントの最小化**: 新規作成するクラス・メソッドは必要最小限とし、既存コンポーネントの拡張で対応
- **既存API拡張**: 新規APIは作成せず、既存の `/api/item/consume` APIを拡張
- **段階的実装**: ItemType追加 → ドメインロジック実装 → テストの順で段階的に実装

**既存実装との整合性:**
- ItemType定義追加により、既存のItemService::apply()のswitch文に新しいcaseを追加
- スタミナ回復計算は既存のUserBuyStaminaService::calcAddStamina()のロジックを流用
- 報酬配布は既存のRewardDelegatorの仕組みをそのまま利用
- ログ記録は既存のLogItemRepository、LogStaminaRepositoryを利用

**重要な判断基準:**
- 既存のコーディング規約（@docs/01_project/coding-standards/api-coding-standards.md）に厳密に従う
- DB接頭辞付き変数命名（`$mstItemId`, `$usrItem`等）を徹底
- return arrayは禁止、EntityまたはValue Objectで返却
- トランザクション制御はUseCase層で実施

### 2.2 実装の全体像

**新規追加されるコンポーネント:**
1. **ItemType追加**: `ItemType::STAMINA_RECOVERY_PERCENT`（Enum）
2. **ItemService拡張**: `applyStaminaRecoveryPercent()`メソッド追加
3. **glow-schema拡張**: ItemType定義にStaminaRecoveryPercentを追加
4. **マスタデータ**: mst_itemsテーブルに新規アイテム追加（運用側で設定）、`effect_value`に回復パーセンテージを指定

**既存コンポーネントへの影響範囲:**
- `api/app/Domain/Item/Services/ItemService.php`: apply()メソッドのswitch文に新規case追加
- `api/app/Domain/Item/Enums/ItemType.php`: 新規Enum値追加
- `glow-schema/Schema/Item.yml`: ItemType定義追加

**段階的実装の方針:**
- **Phase 1（今回）:** `StaminaRecoveryPercent` のみ実装。effect_valueでパーセンテージを指定可能
- **Phase 2（将来必要時）:** `StaminaRecoveryFixed` を追加。effect_valueで固定回復量を指定

**アーキテクチャ上の考慮点:**
- クリーンアーキテクチャに準拠した層分離を維持
- ItemドメインとUserドメインの境界を明確に保つ
- ItemServiceからUserService（スタミナ管理）への依存関係を追加
- トランザクション境界はUseCase層で制御（ItemConsumeUseCase内）

---

## 3. 機能要件別実装設計

### 3.1 アイテム管理要件

#### 要件 ITEM-1: スタミナ回復アイテムの種別定義

##### 3.1.1 要件概要
- **要件ID:** REQ-ITEM-1
- **実現内容:** ItemType Enumに新規値 `STAMINA_RECOVERY_PERCENT` を追加し、パーセンテージ指定のスタミナ回復アイテムを既存アイテムシステムで識別・管理できるようにする

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [x] 既存API改修

本要件ではAPI変更は不要です（ItemType定義の追加のみ）。

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [ ] 新規ドメイン追加
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\Item`

**ファイル構成:**

*改修が必要な既存ファイル:*
- [x] `api/app/Domain/Item/Enums/ItemType.php` - 新規Enum値追加
- [x] `glow-schema/Schema/Item.yml` - ItemType定義追加

**主要な変更内容:**

| クラス/ファイル | 変更内容 | 備考 |
|---------------|---------|------|
| ItemType.php | case STAMINA_RECOVERY_PERCENT = 'StaminaRecoveryPercent'; を追加 | 既存のEtcは使用しない |
| ItemType.php | label()メソッドにmatchケースを追加 | 「スタミナ回復アイテム（%指定）」 |
| Item.yml | ItemType EnumにStaminaRecoveryPercentを追加 | glow-schema側の定義 |

**実装例:**

```php
// api/app/Domain/Item/Enums/ItemType.php
enum ItemType: string
{
    // ... 既存のEnum値 ...

    // スタミナ回復アイテム（パーセンテージ指定）
    case STAMINA_RECOVERY_PERCENT = 'StaminaRecoveryPercent';

    // 将来追加予定: スタミナ回復アイテム（固定量指定）
    // case STAMINA_RECOVERY_FIXED = 'StaminaRecoveryFixed';

    // ... 既存のEnum値 ...
    case ETC = 'Etc';

    public function label(): string
    {
        return match ($this) {
            // ... 既存のmatch ...
            self::STAMINA_RECOVERY_PERCENT => 'スタミナ回復アイテム（%指定）',
            self::ETC => 'その他'
        };
    }
}
```

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

本要件ではDB変更は不要です。既存の `mst_items` テーブルに新しいItemTypeのレコードを追加するのみです（運用側で実施）。

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

本要件では新規エラーコードは不要です。

##### 3.1.6 実装上の注意事項

**既存実装との整合性:**
- 既存のItemType定義（CHARACTER_FRAGMENT, RANDOM_FRAGMENT_BOX等）と同じ命名規則に従う
- Enum値は文字列型（PascalCase）
- label()メソッドは日本語ラベル

**マスタデータに関する考慮点:**
- ItemType定義追加後、mst_itemsテーブルに新規アイテムレコードを追加する必要がある
- `effect_value` に回復パーセンテージを設定する（例: "50" = 50%回復）
- マスタデータはS3経由でクライアントに配信される（マスタデータ配信機構参照）
- サーバーAPIではmst_itemsからの読み取りのみ行う

**段階的実装の方針:**
- **Phase 1（今回）:** `StaminaRecoveryPercent` のみ実装
- **Phase 2（将来必要時）:** `StaminaRecoveryFixed` を追加。effect_valueで固定回復量を指定

---

#### 要件 ITEM-2: アイテム所持数管理

##### 3.1.2 要件概要
- **要件ID:** REQ-ITEM-2
- **実現内容:** 既存のUsrItemServiceを利用してスタミナ回復アイテムの所持数を管理する

##### 3.1.3 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [ ] 既存API改修

本要件では新規実装は不要です。既存のUsrItemServiceがそのまま利用可能です。

##### 3.1.4 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [ ] 新規ドメイン追加
- [ ] 既存ドメイン改修

本要件では新規実装は不要です。既存実装をそのまま利用します。

**既存実装の利用:**

| クラス/ファイル | メソッド | 役割 |
|---------------|---------|------|
| UsrItemService | addItemByRewards() | 報酬配布時の所持数加算 |
| UsrItemService | consumeItem() | アイテム使用時の所持数減算 |
| UsrItemRepository | getListByMstItemIds() | 所持アイテム取得 |
| UsrItemRepository | syncModel() | 所持数更新 |

##### 3.1.5 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

既存の `usr_items` テーブルをそのまま利用します。

##### 3.1.6 エラーハンドリング

**エラーコード定義:**

既存のエラーコードを利用します：

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| ErrorCode::ITEM_NOT_OWNED | アイテム未所持 | 所持数が0のアイテムを使用 | アイテムを所持していません | UsrItemService::consumeItem()で自動チェック |

##### 3.1.7 実装上の注意事項

**既存実装との整合性:**
- UsrItemService::addItemByRewards()は、所持数上限（USER_ITEM_MAX_AMOUNT）を自動チェック
- 上限超過時は自動的にUnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDEDを設定
- ログ記録（log_items）も自動的に実施される

---

#### 要件 ITEM-3: アイテム消費処理

##### 3.1.3 要件概要
- **要件ID:** REQ-ITEM-3
- **実現内容:** 既存のUsrItemService::consumeItem()を利用してアイテムを消費する

##### 3.1.4 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [x] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/api/item/consume`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "mstItemId": "1001",
  "amount": 2
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| mstItemId | string | ○ | アイテムID（スタミナ回復アイテムのマスタID） | required |
| amount | integer | ○ | 使用個数。1以上の整数を指定。**現在スタミナがスタミナ上限未満であれば複数個同時指定可能**（回復後のスタミナはスタミナ上限を超えてもOK） | required, integer, min:1 |

**複数個同時使用について（2025-12-02追加確認）:**
- 既存のプリズム・広告視聴によるスタミナ回復のUIとは異なり、**個数選択UIを実装**
- **現在スタミナがスタミナ上限未満**であれば、**1度に2つ以上消費して回復可能**
- **回復後のスタミナがスタミナ上限を超えることは許可**（システム上限999まで）
- 例：スタミナが50/180の場合（アイテム1個で+90回復）
  - 1個目使用: 50+90=140（< 180なので、まだ2個目も使用可能）
  - 2個目使用: 140+90=230（> 180だが、**230まで回復OK**）
  - 2個目使用後は「現在スタミナ230」>「スタミナ上限180」なので、3個目は使用不可

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "usrParameter": {
      "level": 15,
      "exp": 1200,
      "coin": 50000,
      "stamina": 150,
      "staminaUpdatedAt": "2025-11-27T10:30:00+09:00",
      "freeDiamond": 100,
      "paidDiamondIos": 0,
      "paidDiamondAndroid": 0
    },
    "usrItems": [
      {
        "mstItemId": "item_stamina_recovery_001",
        "amount": 9
      }
    ],
    "itemRewards": [],
    "usrItemTrade": null
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | boolean | 処理結果 |
| data.usrParameter | object | 使用後のユーザーパラメータ（スタミナ含む） |
| data.usrParameter.stamina | int | **更新後のスタミナ値**（自然回復 + アイテム回復後の値） |
| data.usrParameter.staminaUpdatedAt | string | **スタミナ更新日時**（アイテム使用時刻） |
| data.usrItems | array | 使用後のアイテム一覧 |
| data.itemRewards | array | 獲得報酬（スタミナ回復アイテムの場合は空配列） |
| data.usrItemTrade | null | アイテム交換情報（スタミナ回復アイテムの場合はnull） |

##### 3.1.5 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [ ] 新規ドメイン追加
- [x] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\Item`

**ファイル構成:**

*改修が必要な既存ファイル:*
- [x] `api/app/Domain/Item/Services/ItemService.php` - apply()メソッドに新規case追加、applyStaminaRecoveryPercent()メソッド追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ItemService | apply() | アイテム種別別の処理分岐 | switch文にSTAMINA_RECOVERY_PERCENTのcase追加 |
| ItemService | applyStaminaRecoveryPercent() | スタミナ回復処理（パーセンテージ指定）の実装 | 新規メソッド（private）、effect_valueから回復%取得 |
| UsrItemService | consumeItem() | アイテム消費処理 | 既存メソッドを呼び出し |
| UserService | recoveryStamina() | 自然回復適用 | 既存メソッドを呼び出し |
| UserService | addStamina() | スタミナ加算 | 既存メソッドを呼び出し |

**実装例:**

```php
// api/app/Domain/Item/Services/ItemService.php

public function __construct(
    // ... 既存のコンストラクタ引数 ...
    // 以下を追加
    private UserService $userService,
    private UserBuyStaminaService $userBuyStaminaService,
) {
}

public function apply(
    string $userId,
    int $platform,
    MstItemEntity $mstItem,
    int $amount,
    CarbonImmutable $now
): ?UsrItemTradeInterface {
    $usrItemTrade = null;

    switch ($mstItem->getItemType()) {
        case ItemType::RANDOM_FRAGMENT_BOX->value:
            // ... 既存処理 ...
            break;
        case ItemType::CHARACTER_FRAGMENT->value:
            // ... 既存処理 ...
            break;
        case ItemType::STAMINA_RECOVERY_PERCENT->value:
            // スタミナ回復アイテム処理（パーセンテージ指定）
            $this->applyStaminaRecoveryPercent(
                $userId,
                $mstItem,
                $amount,
                $now
            );
            break;
        default:
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "invalid item type. (itemType: {$mstItem->getItemType()})"
            );
    }

    return $usrItemTrade;
}

/**
 * スタミナ回復アイテム（パーセンテージ指定）の使用処理
 *
 * @param string $userId
 * @param MstItemEntity $mstItem effect_valueに回復パーセンテージを持つ
 * @param int $amount 使用個数
 * @param CarbonImmutable $now
 * @throws GameException
 */
private function applyStaminaRecoveryPercent(
    string $userId,
    MstItemEntity $mstItem,
    int $amount,
    CarbonImmutable $now
): void {
    // 1. 自然回復を適用
    $usrUserParameter = $this->userService->recoveryStamina($userId, $now);

    // 2. スタミナ満タンチェック
    $currentStamina = $usrUserParameter->getStamina();
    $maxStamina = $this->calculateMaxStamina($userId, $usrUserParameter->getLevel(), $now);

    if ($maxStamina <= $currentStamina) {
        throw new GameException(
            ErrorCode::USER_STAMINA_FULL,
            "stamina is full. (stamina: $currentStamina)"
        );
    }

    // 3. スタミナ回復量を計算（effect_valueから回復パーセンテージを取得）
    $recoveryStaminaPercentage = (int) $mstItem->getEffectValue(); // effect_valueから取得（例: "50" = 50%回復）
    $addStamina = (int) floor($maxStamina * $recoveryStaminaPercentage / 100) * $amount;

    // 4. システム上限チェック（システム上限999まで許可、スタミナ上限での打ち切りはしない）
    $systemMaxStamina = UserConstant::MAX_STAMINA; // 999
    $afterStamina = min($currentStamina + $addStamina, $systemMaxStamina);
    $actualAddStamina = $afterStamina - $currentStamina;
    // ※スタミナ上限（例: 180）での打ち切りはしない
    // ※回復後スタミナがスタミナ上限を超えることは正常動作

    // 5. アイテム消費（ログ記録含む）
    $this->usrItemService->consumeItem(
        $userId,
        $mstItem->getId(),
        $amount,
        new ItemStaminaRecoveryLogTrigger($mstItem->getId(), $amount, $actualAddStamina),
    );

    // 6. スタミナ加算（ログ記録含む）
    $this->userService->addStamina($userId, $actualAddStamina, $now);
}

/**
 * ユーザーのスタミナ上限値を計算（レベル別上限 + ショップパス効果）
 */
private function calculateMaxStamina(
    string $userId,
    int $level,
    CarbonImmutable $now
): int {
    $levelStamina = $this->mstUserLevelRepository
        ->getByLevel($level, true)
        ->getStamina();

    $shopPassEffectData = $this->shopPassEffectDelegator
        ->getShopPassActiveEffectDataByUsrUserId($userId, $now);

    return $levelStamina + $shopPassEffectData->getStaminaAddRecoveryLimit();
}
```

##### 3.1.6 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

既存のテーブル（usr_items、log_items、log_staminas）をそのまま利用します。

##### 3.1.7 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | クライアント挙動 | 実装箇所 |
|-------------|---------|---------|-----------------|---------|
| ErrorCode::USER_STAMINA_FULL (4003) | スタミナが満タンで使用不可 | 自然回復適用後のスタミナ値 ≥ スタミナ上限値 | エラーダイアログを表示「スタミナが満タンです」<br/>アイテムは消費されない<br/>ホーム画面に戻る | applyStaminaRecoveryPercent()内 |
| ErrorCode::ITEM_NOT_OWNED (2001) | アイテム未所持 | 指定されたアイテムを所持していない<br/>または所持数が使用個数より少ない | エラーダイアログを表示「アイテムが不足しています」<br/>アイテムは消費されない<br/>アイテム一覧を再取得して更新 | UsrItemService::consumeItem()で自動チェック |
| ErrorCode::USER_STAMINA_EXCEEDS_LIMIT (4004) | システム上限超過（理論上発生しない） | 回復後のスタミナ値が999を超える<br/>（UI側制御により通常は発生しない） | エラーダイアログを表示「システムエラー」<br/>アイテムは消費されない<br/>ホーム画面に戻る | applyStaminaRecoveryPercent()内（オプション） |
| ErrorCode::MST_NOT_FOUND (10) | マスタデータ未存在 | 指定されたmstItemIdが存在しない | エラーダイアログを表示「データが見つかりません」<br/>ホーム画面に戻る | ItemConsumeUseCase内 |
| ErrorCode::VALIDATION_ERROR (2) | リクエストパラメータ不正 | mstItemIdが空、またはamountが0以下 | エラーダイアログを表示「不正なリクエストです」<br/>ホーム画面に戻る | Controllerバリデーション |

**エラーハンドリング方針:**
- クライアント側で事前にスタミナ満タン状態をチェックし、満タン時はアイテム使用ボタンをグレーアウト
- **使用可否判定**: 現在スタミナ < スタミナ上限 の場合のみ使用可能
- **回復量制限**: システム上限999まで許可（スタミナ上限での打ち切りはしない）
- サーバー側のエラーチェックは二重チェックとして機能し、クライアント側実装ミスやチート対策となる

**エラーハンドリングの実装方針:**
- エラーはService層でthrowする（applyStaminaRecoveryPercent()内）
- UseCase層でトランザクションロールバック
- Controller層でエラーレスポンスに変換

##### 3.1.8 実装上の注意事項

**パフォーマンス考慮点:**
- N+1問題の回避: UsrItemRepository::getListByMstItemIds()は既にバルク取得に対応
- インデックスの活用: usr_itemsテーブルのPRIMARY KEY (usr_user_id, mst_item_id) を利用
- キャッシュ戦略: UsrItemRepositoryはキャッシュ機構を持つため追加実装不要

**セキュリティ考慮点:**
- 入力値検証: Controllerレイヤーで実施（Laravel標準バリデーション）
- 権限チェック: 認証済みユーザーのみ実行可能
- 不正防止: 所持数チェック、スタミナ満タンチェックをサーバー側で実施

**データ整合性:**
- トランザクション制御: ItemConsumeUseCase内で実施
- ロック戦略: 楽観ロック（Eloquentのupdated_atによる自動チェック）
- ロールバック処理: トランザクション失敗時は全ての変更をロールバック

**既存実装との整合性:**
- 類似機能との関係: UserBuyStaminaService（広告視聴スタミナ購入）と同じスタミナ回復パターン
- 既存パターンの踏襲: ItemService::applyRandomFragmentBox()と同じ構造
- 影響範囲: 既存のアイテム消費処理に影響なし（新規caseの追加のみ）

---

### 3.2 スタミナ回復要件

#### 要件 STA-1: スタミナ回復量の計算

##### 3.2.1 要件概要
- **要件ID:** REQ-STA-1
- **実現内容:** スタミナ上限値のN%（effect_valueで指定）を回復量として計算する（既存のUserBuyStaminaServiceのロジックを流用）

##### 3.2.2 実装設計

**実装方針:**
- UserBuyStaminaService::calcAddStamina()と同じ計算ロジックを使用
- レベル別スタミナ上限（mst_user_levels.stamina）を取得
- ショップパス効果による追加上限を加算
- **effect_valueから回復パーセンテージを取得**し、上限値のN%を計算（floor関数で切り捨て）

**実装例:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// スタミナ上限値を計算（レベル別上限 + ショップパス効果）
$levelStamina = $this->mstUserLevelRepository
    ->getByLevel($usrUserParameter->getLevel(), true)
    ->getStamina();

$shopPassEffectData = $this->shopPassEffectDelegator
    ->getShopPassActiveEffectDataByUsrUserId($userId, $now);

$maxStamina = $levelStamina + $shopPassEffectData->getStaminaAddRecoveryLimit();

// スタミナ上限値のN%を計算（effect_valueから回復パーセンテージを取得）
$recoveryStaminaPercentage = (int) $mstItem->getEffectValue(); // 例: "50" = 50%回復
$addStamina = (int) floor($maxStamina * $recoveryStaminaPercentage / 100) * $amount;
```

##### 3.2.3 既存実装との整合性

**参考実装:**
- `api/app/Domain/User/Services/UserBuyStaminaService.php` の `calcAddStamina()`
- 広告視聴スタミナ購入（BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA = 50）と同じ計算式

---

#### 要件 STA-2: 自然回復の適用

##### 3.2.2 要件概要
- **要件ID:** REQ-STA-2
- **実現内容:** アイテム使用前に自然回復分を先に適用する（既存のUserService::recoveryStamina()を利用）

##### 3.2.3 実装設計

**実装方針:**
- ItemService::applyStaminaRecoveryPercent()の最初に UserService::recoveryStamina() を呼び出す
- 既存実装がスタミナ値とstamina_updated_atを自動更新する
- 返却されたUsrUserParameterInterfaceから最新のスタミナ値を取得

**実装例:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// 1. 自然回復を適用
$usrUserParameter = $this->userService->recoveryStamina($userId, $now);

// 2. 自然回復適用後のスタミナ値を取得
$currentStamina = $usrUserParameter->getStamina();
```

---

#### 要件 STA-3: スタミナ満タン時の使用制限

##### 3.2.3 要件概要
- **要件ID:** REQ-STA-3
- **実現内容:** スタミナが上限値以上の時、ErrorCode::USER_STAMINA_FULLをthrowする

##### 3.2.4 実装設計

**実装方針:**
- 自然回復適用後の現在スタミナと上限値を比較
- `スタミナ上限値 <= 現在スタミナ` の場合、GameException をthrow
- エラー発生時はトランザクションがロールバックされ、アイテムは消費されない

**実装例:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// 2. スタミナ満タンチェック
$currentStamina = $usrUserParameter->getStamina();
$maxStamina = $this->calculateMaxStamina($userId, $usrUserParameter->getLevel(), $now);

if ($maxStamina <= $currentStamina) {
    throw new GameException(
        ErrorCode::USER_STAMINA_FULL,
        "stamina is full. (stamina: $currentStamina)"
    );
}
```

---

#### 要件 STA-4: システム上限値制御

##### 3.2.4 要件概要
- **要件ID:** REQ-STA-4
- **実現内容:** スタミナ回復後の値がシステム絶対上限（999）を超えないようmin処理で制御

##### 3.2.5 実装設計

**実装方針:**
- UserConstant::MAX_STAMINA（999）を上限値とする
- 現在スタミナ + 回復量がシステム上限を超える場合、999で打ち切る
- **注意: スタミナ上限（例: 180）での打ち切りはしない**（回復後スタミナがスタミナ上限を超えることは正常動作）

**実装例:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// 4. システム上限チェック（システム上限999まで許可、スタミナ上限での打ち切りはしない）
$systemMaxStamina = UserConstant::MAX_STAMINA; // 999
$afterStamina = min($currentStamina + $totalAddStamina, $systemMaxStamina);
$actualAddStamina = $afterStamina - $currentStamina;
// ※スタミナ上限（例: 180）での打ち切りはしない
```

---

#### 要件 STA-5: スタミナ加算処理

##### 3.2.5 要件概要
- **要件ID:** REQ-STA-5
- **実現内容:** 計算された回復量をUserService::addStamina()で加算する

##### 3.2.6 実装設計

**実装方針:**
- UserService::addStamina()を呼び出してスタミナ加算
- 既存実装がusrテーブルの更新とlog_staminasへのログ記録を自動実施
- stamina_updated_atも自動更新される

**実装例:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// 6. スタミナ加算（ログ記録含む）
$this->userService->addStamina($userId, $actualAddStamina, $now);
```

---

### 3.3 報酬配布要件

#### 要件 REWARD-1 ~ REWARD-4: 各種報酬配布

##### 3.3.1 要件概要
- **要件ID:** REQ-REWARD-1, REQ-REWARD-2, REQ-REWARD-3, REQ-REWARD-4
- **実現内容:** 既存のRewardDelegatorを利用して、ログインボーナス、ミッション、イベント、お詫びでスタミナ回復アイテムを配布

##### 3.3.2 実装設計

**実装方針:**
- 新規実装は不要
- 既存のRewardDelegator → ItemSendService → UsrItemService::addItemByRewards()の流れがそのまま利用可能
- マスタデータ（ログインボーナス、ミッション報酬等）に新しいItemTypeのアイテムIDを設定するだけで動作

**既存実装の利用:**

| 配布経路 | 既存の仕組み | 備考 |
|---------|------------|------|
| ログインボーナス | LoginBonusService → RewardDelegator | マスタデータで設定可能 |
| ミッション報酬 | MissionService → RewardDelegator | マスタデータで設定可能 |
| イベント報酬 | EventService → RewardDelegator | マスタデータで設定可能 |
| お詫び配布 | CompensationService → RewardDelegator | 運営ツールから配布可能 |

---

#### 要件 REWARD-5: 報酬配布時の上限処理

##### 3.3.5 要件概要
- **要件ID:** REQ-REWARD-5
- **実現内容:** 既存のUsrItemService::addItemByRewards()が上限処理を自動実施

##### 3.3.6 実装設計

**実装方針:**
- 新規実装は不要
- UsrItemService::addItemByRewards()が所持数上限（USER_ITEM_MAX_AMOUNT）を自動チェック
- 上限超過時は自動的にUnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDEDを設定

**既存実装の動作:**

```php
// UsrItemService::addItemByRewards() の既存実装

$maxAmount = $this->mstConfigService->getUserItemMaxAmount();

// ...

if ($afterAmount > $maxAmount) {
    $reward->setUnreceivedRewardReason(UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED);
    $afterAmount = $maxAmount;
}
```

---

### 3.4 API実行要件

#### 要件 API-1: アイテム使用API

##### 3.4.1 要件概要
- **要件ID:** REQ-API-1
- **実現内容:** 既存の `/api/item/consume` APIを拡張し、スタミナ回復アイテムに対応

##### 3.4.2 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [x] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/api/item/consume`
- HTTPメソッド: POST
- 認証: 必要

**リクエストパラメータ（JSON形式）:**
```json
{
  "mstItemId": "1001",
  "amount": 2
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| mstItemId | string | ○ | スタミナ回復アイテムのマスタID | required |
| amount | integer | ○ | 使用個数 | required, integer, min:1 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "usrItems": [
      {
        "mstItemId": "1001",
        "amount": 8
      }
    ],
    "usrParameter": {
      "stamina": 150,
      "staminaUpdatedAt": "2025-11-27T12:00:00+09:00",
      "level": 10,
      "exp": 1000,
      "coin": 10000,
      "diamond": 500
    },
    "itemRewards": [],
    "usrItemTrade": null
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | boolean | 処理結果 |
| data.usrItems | array | 使用後のアイテム一覧 |
| data.usrParameter | object | 使用後のユーザーパラメータ（スタミナ含む） |
| data.itemRewards | array | 獲得報酬（スタミナ回復の場合は空配列） |
| data.usrItemTrade | null | アイテム交換情報（スタミナ回復では不要） |

##### 3.4.3 ドメイン設計

**改修が必要な既存ファイル:**
- [ ] `api/app/Http/Controllers/Api/V1/Game/Item/ItemController.php` - 変更不要（既存のconsume()メソッドがそのまま動作）
- [x] `api/app/Domain/Item/Services/ItemService.php` - apply()メソッドに新規case追加

**処理フロー:**

```
Controller::consume()
  ↓
ItemConsumeUseCase::exec() [トランザクション開始]
  ↓
ItemService::apply()
  ↓ (ItemType::STAMINA_RECOVERY_PERCENT の場合)
ItemService::applyStaminaRecoveryPercent()
  ├→ UserService::recoveryStamina() [自然回復適用]
  ├→ スタミナ満タンチェック
  ├→ スタミナ回復量計算
  ├→ システム上限チェック
  ├→ UsrItemService::consumeItem() [アイテム消費 + ログ記録]
  └→ UserService::addStamina() [スタミナ加算 + ログ記録]
  ↓
[トランザクション終了]
  ↓
ResponseFactory::createConsumeResponse()
```

##### 3.4.4 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | HTTPステータス | ユーザーへのメッセージ |
|-------------|---------|---------|---------------|---------------------|
| ErrorCode::ITEM_NOT_OWNED | アイテム未所持 | 所持数不足 | 400 | アイテムを所持していません |
| ErrorCode::USER_STAMINA_FULL | スタミナ満タン | スタミナ上限以上 | 400 | スタミナが満タンです |
| ErrorCode::INVALID_PARAMETER | 不正なパラメータ | 不正なItemType | 400 | 不正なアイテムです |

**エラーレスポンス形式:**

```json
{
  "result": false,
  "error": {
    "code": "USER_STAMINA_FULL",
    "message": "スタミナが満タンです"
  }
}
```

##### 3.4.5 実装上の注意事項

**トランザクション制御:**
- ItemConsumeUseCase内でトランザクション開始
- エラー発生時は自動ロールバック
- アイテム消費、スタミナ加算、ログ記録が全てロールバック対象

**既存実装との整合性:**
- ItemController::consume()は変更不要
- ItemConsumeUseCaseも変更不要
- ItemService::apply()のswitch文に新規caseを追加するのみ

---

#### 要件 API-2: アイテム種別別処理の実装

##### 3.4.2 要件概要
- **要件ID:** REQ-API-2
- **実現内容:** ItemService::apply()メソッドにSTAMINA_RECOVERY_PERCENTのcase分岐を追加

##### 3.4.3 実装設計

**実装方針:**
- 既存のswitch文に新規caseを追加
- applyStaminaRecoveryPercent()メソッドを呼び出し
- 処理フローは「要件 ITEM-3」で詳述済み

**実装例:**

```php
// ItemService::apply() 内での実装

switch ($mstItem->getItemType()) {
    case ItemType::RANDOM_FRAGMENT_BOX->value:
        // ... 既存処理 ...
        break;
    case ItemType::CHARACTER_FRAGMENT->value:
        // ... 既存処理 ...
        break;
    case ItemType::STAMINA_RECOVERY_PERCENT->value:
        // スタミナ回復アイテム処理（パーセンテージ指定）
        $this->applyStaminaRecoveryPercent(
            $userId,
            $mstItem,
            $amount,
            $now
        );
        break;
    default:
        throw new GameException(
            ErrorCode::INVALID_PARAMETER,
            "invalid item type. (itemType: {$mstItem->getItemType()})"
        );
}
```

---

### 3.5 ログ記録要件

#### 要件 LOG-1: アイテム消費ログ

##### 3.5.1 要件概要
- **要件ID:** REQ-LOG-1
- **実現内容:** 既存のUsrItemService::consumeItem()が自動的にlog_itemsテーブルにログを記録

##### 3.5.2 実装設計

**実装方針:**
- 新規実装は不要
- UsrItemService::consumeItem()内でLogItemRepository::make()が自動実行される
- LogTriggerとして ItemStaminaRecoveryLogTrigger を新規作成

**LogTrigger実装例:**

```php
// api/app/Domain/Resource/Entities/LogTriggers/ItemStaminaRecoveryLogTrigger.php

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ItemStaminaRecoveryLogTrigger extends LogTrigger
{
    public function __construct(
        private string $mstItemId,
        private int $amount,
        private int $addStamina,
    ) {
        parent::__construct(LogResourceTriggerSource::ITEM_STAMINA_RECOVERY);
    }

    public function getLogTriggerData(): array
    {
        return [
            'trigger_source' => $this->triggerSource->value,
            'mst_item_id' => $this->mstItemId,
            'amount' => $this->amount,
            'add_stamina' => $this->addStamina,
        ];
    }
}
```

**LogResourceTriggerSource Enum追加:**

```php
// api/app/Domain/Resource/Log/Enums/LogResourceTriggerSource.php

enum LogResourceTriggerSource: string
{
    // ... 既存の値 ...

    // スタミナ回復アイテム使用
    case ITEM_STAMINA_RECOVERY = 'ItemStaminaRecovery';
}
```

**ログ記録内容:**

| カラム | 値 | 説明 |
|-------|-----|------|
| usr_user_id | $userId | ユーザーID |
| action_type | LogResourceActionType::USE | アクション種別（使用） |
| mst_item_id | $mstItemId | アイテムID |
| before_amount | $beforeAmount | 消費前の所持数 |
| after_amount | $afterAmount | 消費後の所持数 |
| trigger_data | JSON | トリガー情報（詳細データ） |

---

#### 要件 LOG-2: スタミナ増加ログ

##### 3.5.2 要件概要
- **要件ID:** REQ-LOG-2
- **実現内容:** UserService::addStamina()が自動的にlog_staminasテーブルにログを記録

##### 3.5.3 実装設計

**実装方針:**
- 新規実装は不要
- UserService::addStamina()内でLogStaminaRepository::create()が自動実行される
- トリガー情報に「スタミナ回復アイテム使用」を記録

**ログ記録内容:**

| カラム | 値 | 説明 |
|-------|-----|------|
| usr_user_id | $userId | ユーザーID |
| action_type | LogResourceActionType::ADD | アクション種別（加算） |
| before_amount | $beforeStamina | 回復前のスタミナ値 |
| after_amount | $afterStamina | 回復後のスタミナ値 |
| trigger_data | JSON | トリガー情報（アイテムID、使用個数等） |

**既存実装の確認:**

UserService::addStamina()の既存実装を確認したところ、ログ記録処理が含まれていない可能性があります。この場合、以下の対応が必要です：

**対応方針:**
1. UserService::addStamina()にログ記録処理を追加
2. または、ItemService::applyStaminaRecovery()内で直接LogStaminaRepository::create()を呼び出す

**実装例（方針2の場合）:**

```php
// ItemService::applyStaminaRecoveryPercent() 内での実装

// スタミナ加算前の値を保存
$beforeStamina = $usrUserParameter->getStamina();

// スタミナ加算
$this->userService->addStamina($userId, $actualAddStamina, $now);

// スタミナ増加ログを記録
$this->logStaminaRepository->create(
    $userId,
    LogResourceActionType::ADD,
    $beforeStamina,
    $beforeStamina + $actualAddStamina,
    [
        'trigger_source' => LogResourceTriggerSource::ITEM_STAMINA_RECOVERY->value,
        'mst_item_id' => $mstItem->getId(),
        'amount' => $amount,
        'add_stamina' => $actualAddStamina,
    ],
);
```

**依存性注入の追加:**

```php
// ItemService コンストラクタに追加

public function __construct(
    // ... 既存の引数 ...
    private LogStaminaRepository $logStaminaRepository,
) {
}
```

---

### 3.6 ミッション連携要件

#### 要件 MISSION-1: アイテム獲得時のミッショントリガー

##### 3.6.1 要件概要
- **要件ID:** REQ-MISSION-1
- **実現内容:** 既存のUsrItemService::addItemByRewards()が自動的にミッショントリガーを送信

##### 3.6.2 実装設計

**実装方針:**
- 新規実装は不要
- UsrItemService::addItemByRewards()内でItemMissionTriggerService::sendItemCollectTrigger()が自動実行される
- 「特定のアイテムを獲得する」ミッションに対応

**既存実装の動作:**

```php
// UsrItemService::addItemByRewards() の既存実装

// ミッショントリガー送信
$this->itemMissionTriggerService->sendItemCollectTrigger($mstItemId, $addAmount);
```

---

#### 要件 MISSION-2: アイテム使用時のミッショントリガー（実装不要）

##### 3.6.2 要件概要
- **要件ID:** REQ-MISSION-2
- **実現内容:** 実装不要（ゲーム体験仕様確認結果Q4の回答に基づく）

##### 3.6.3 実装設計

**実装方針:**
- 実装不要
- 「スタミナ回復アイテムを使用する」ミッションは存在しないため、トリガー送信は不要

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（1日目）**
1. REQ-ITEM-1: ItemType定義追加（glow-schema、api両方）
   - `glow-schema/Schema/Item.yml` にItemType追加
   - `api/app/Domain/Item/Enums/ItemType.php` にEnum値追加
2. LogResourceTriggerSource Enum追加
   - `api/app/Domain/Resource/Log/Enums/LogResourceTriggerSource.php` に値追加
3. LogTriggerクラス作成
   - `api/app/Domain/Resource/Entities/LogTriggers/ItemStaminaRecoveryLogTrigger.php` 作成

**フェーズ2: コア機能実装（2日目）**
1. REQ-ITEM-3, REQ-STA-1~5, REQ-API-2: ItemService拡張
   - `ItemService::applyStaminaRecoveryPercent()` メソッド実装
   - `ItemService::apply()` にswitch case追加
   - `ItemService::calculateMaxStamina()` メソッド実装
2. 依存性注入の追加
   - ItemServiceコンストラクタに必要な依存関係を追加

**フェーズ3: テスト実装（3日目）**
1. ユニットテスト実装
   - `ItemServiceTest::testApplyStaminaRecovery()`
   - 正常系・異常系のテストケース
2. 機能テスト実装
   - `/api/item/consume` APIの機能テスト
   - スタミナ回復の動作確認
3. シナリオテスト実装
   - 報酬配布→アイテム獲得→使用の一連の流れ

**フェーズ4: 動作確認・統合テスト（4日目）**
1. sail checkによる品質チェック
   - phpcs/phpcbf（コーディング規約）
   - phpstan（静的解析）
   - deptrac（アーキテクチャ）
   - phpunit（テスト）
2. 手動動作確認
   - ローカル環境でのAPI実行
   - ログ記録の確認
   - エラーケースの確認

### 4.2 依存関係マップ

```
REQ-ITEM-1 (ItemType定義)
  ↓
REQ-API-2 (ItemService::apply()拡張)
  ↓
REQ-ITEM-3, REQ-STA-1~5 (applyStaminaRecoveryPercent()実装)
  ├→ REQ-LOG-1 (アイテム消費ログ)
  └→ REQ-LOG-2 (スタミナ増加ログ)
  ↓
REQ-REWARD-1~5 (報酬配布) ※既存実装で自動対応
REQ-MISSION-1 (ミッショントリガー) ※既存実装で自動対応
```

### 4.3 実装時の注意点

- **フェーズ1を完了してからフェーズ2に進む**: ItemType定義がないとコンパイルエラー
- **各フェーズ内でも依存関係を考慮**: LogTriggerクラス作成後にItemService実装
- **テストは各要件実装後に都度実施**: 機能単位でテストを書いて即座に検証
- **sail checkは最後に実施**: 全実装完了後に品質チェック

---

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- `ItemService::applyStaminaRecoveryPercent()` メソッド
- `ItemService::calculateMaxStamina()` メソッド

**テストケース例:**

| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| applyStaminaRecoveryPercent() | 正常値を入力（effect_value=50でスタミナ50%回復） | 正しい回復量が加算される |
| applyStaminaRecoveryPercent() | スタミナ満タン状態で実行 | USER_STAMINA_FULL例外がthrowされる |
| applyStaminaRecoveryPercent() | 所持数不足のアイテムを使用 | ITEM_NOT_OWNED例外がthrowされる |
| applyStaminaRecoveryPercent() | スタミナ上限を超える回復 | **システム上限999まで許可**（スタミナ上限180での打ち切りはしない） |
| applyStaminaRecoveryPercent() | **amount=2 で複数個同時使用**（スタミナ50/180、1個90回復） | 2個消費され、50+180=**230**まで回復（スタミナ上限180を超えてもOK） |
| applyStaminaRecoveryPercent() | **amount=10 でシステム上限超過を試みる**（スタミナ50/180、1個90回復） | システム上限999で打ち切られる（50+900→999） |
| applyStaminaRecoveryPercent() | effect_value=30で30%回復 | effect_valueに応じた回復量が加算される |
| calculateMaxStamina() | ショップパス効果なし | レベル別スタミナ上限が返る |
| calculateMaxStamina() | ショップパス効果あり | レベル別上限+ショップパス効果が返る |

**テストファイル:**
- `api/tests/Unit/Domain/Item/Services/ItemServiceTest.php`

**テスト実装例:**

```php
public function testApplyStaminaRecoveryPercentSuccess(): void
{
    // Arrange
    $userId = 'test_user_001';
    $mstItemId = '1001';
    $amount = 1;
    $now = CarbonImmutable::now();

    // スタミナ: 50/200 の状態を作成
    $usrUserParameter = Mockery::mock(UsrUserParameterInterface::class);
    $usrUserParameter->shouldReceive('getStamina')->andReturn(50);
    $usrUserParameter->shouldReceive('getLevel')->andReturn(10);

    // MstItemEntityのモック（effect_value=50 = 50%回復）
    $mstItem = Mockery::mock(MstItemEntity::class);
    $mstItem->shouldReceive('getEffectValue')->andReturn('50');

    // UserService::recoveryStamina() のモック
    $this->userService
        ->shouldReceive('recoveryStamina')
        ->once()
        ->with($userId, $now)
        ->andReturn($usrUserParameter);

    // ... 他のモック設定 ...

    // Act
    $this->itemService->applyStaminaRecoveryPercent($userId, $mstItem, $amount, $now);

    // Assert
    // UserService::addStamina() が正しい引数で呼ばれたことを検証
    $this->userService
        ->shouldHaveReceived('addStamina')
        ->once()
        ->with($userId, 100, $now); // 200スタミナ上限の50% = 100
}

public function testApplyStaminaRecoveryPercentMultipleAmount(): void
{
    // Arrange - amount=2を指定し、スタミナ上限を超えた回復を確認
    $userId = 'test_user_001';
    $mstItemId = '1001';
    $amount = 2; // 2個を指定
    $now = CarbonImmutable::now();

    // スタミナ: 50/180 の状態を作成
    $usrUserParameter = Mockery::mock(UsrUserParameterInterface::class);
    $usrUserParameter->shouldReceive('getStamina')->andReturn(50);
    $usrUserParameter->shouldReceive('getLevel')->andReturn(10);

    // MstItemEntityのモック（effect_value=50 = 50%回復）
    $mstItem = Mockery::mock(MstItemEntity::class);
    $mstItem->shouldReceive('getEffectValue')->andReturn('50');
    $mstItem->shouldReceive('getId')->andReturn($mstItemId);

    // ... 他のモック設定（レベル180のスタミナ上限を返す）...

    // Act
    $this->itemService->applyStaminaRecoveryPercent($userId, $mstItem, $amount, $now);

    // Assert - consumeItemは2個で呼ばれる
    $this->usrItemService
        ->shouldHaveReceived('consumeItem')
        ->once()
        ->with($userId, $mstItemId, 2, Mockery::any()); // 指定通り2個消費

    // Assert - addStaminaは2倍の回復量で呼ばれる
    // 180 * 50% * 2 = 180、回復後 50 + 180 = 230（スタミナ上限180を超えてもOK）
    $this->userService
        ->shouldHaveReceived('addStamina')
        ->once()
        ->with($userId, 180, $now); // 50%回復 × 2個 = 180（スタミナ上限での打ち切りなし）
}

public function testApplyStaminaRecoveryPercentWhenStaminaFull(): void
{
    // Arrange
    $userId = 'test_user_001';
    $mstItemId = '1001';
    $amount = 1;
    $now = CarbonImmutable::now();

    // スタミナ満タン: 200/200 の状態を作成
    $usrUserParameter = Mockery::mock(UsrUserParameterInterface::class);
    $usrUserParameter->shouldReceive('getStamina')->andReturn(200);
    $usrUserParameter->shouldReceive('getLevel')->andReturn(10);

    // MstItemEntityのモック（effect_value=50 = 50%回復）
    $mstItem = Mockery::mock(MstItemEntity::class);
    $mstItem->shouldReceive('getEffectValue')->andReturn('50');

    // UserService::recoveryStamina() のモック
    $this->userService
        ->shouldReceive('recoveryStamina')
        ->once()
        ->with($userId, $now)
        ->andReturn($usrUserParameter);

    // ... 他のモック設定 ...

    // Act & Assert
    $this->expectException(GameException::class);
    $this->expectExceptionCode(ErrorCode::USER_STAMINA_FULL);

    $this->itemService->applyStaminaRecoveryPercent($userId, $mstItem, $amount, $now);
}
```

### 5.2 機能テスト

**テスト対象:**
- `/api/item/consume` APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**

| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /api/item/consume | 正常なリクエスト（スタミナ回復アイテム） | 200 OK、usrParameter.staminaが増加 |
| POST /api/item/consume | スタミナ満タン時のリクエスト | 400 Bad Request、USER_STAMINA_FULLエラー |
| POST /api/item/consume | 所持数不足のリクエスト | 400 Bad Request、ITEM_NOT_OWNEDエラー |
| POST /api/item/consume | 不正なmstItemId | 400 Bad Request、MST_NOT_FOUNDエラー |
| POST /api/item/consume | amount=0のリクエスト | 400 Bad Request、バリデーションエラー |

**テストファイル:**
- `api/tests/Feature/Http/Controllers/Api/V1/Game/Item/ItemControllerTest.php`

**テスト実装例:**

```php
public function testConsumeStaminaRecoveryItemSuccess(): void
{
    // Arrange
    $user = $this->createTestUser();
    $mstItemId = '1001'; // スタミナ回復アイテムのID

    // ユーザーにアイテムを付与（10個）
    $this->createUsrItem($user->getId(), $mstItemId, 10);

    // ユーザーのスタミナを50/200に設定
    $this->setUserStamina($user->getId(), 50);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/item/consume', [
            'mstItemId' => $mstItemId,
            'amount' => 1,
        ]);

    // Assert
    $response->assertStatus(200);
    $response->assertJson([
        'result' => true,
        'data' => [
            'usrItems' => [
                [
                    'mstItemId' => $mstItemId,
                    'amount' => 9, // 10 - 1 = 9
                ],
            ],
            'usrParameter' => [
                'stamina' => 150, // 50 + (200 * 0.5) = 150
            ],
            'itemRewards' => [],
            'usrItemTrade' => null,
        ],
    ]);

    // DBの状態を検証
    $this->assertDatabaseHas('usr_items', [
        'usr_user_id' => $user->getId(),
        'mst_item_id' => $mstItemId,
        'amount' => 9,
    ]);

    $this->assertDatabaseHas('usr_user_parameters', [
        'usr_user_id' => $user->getId(),
        'stamina' => 150,
    ]);

    // ログが記録されているか検証
    $this->assertDatabaseHas('log_items', [
        'usr_user_id' => $user->getId(),
        'mst_item_id' => $mstItemId,
        'action_type' => LogResourceActionType::USE->value,
        'before_amount' => 10,
        'after_amount' => 9,
    ]);

    $this->assertDatabaseHas('log_staminas', [
        'usr_user_id' => $user->getId(),
        'action_type' => LogResourceActionType::ADD->value,
        'before_amount' => 50,
        'after_amount' => 150, // 50 + 100 = 150
    ]);
}
```

### 5.3 シナリオテスト

**テストシナリオ例:**

**シナリオ1: ログインボーナス獲得→アイテム使用→スタミナ回復**
1. ユーザーがログインする
2. ログインボーナスとしてスタミナ回復アイテムを5個獲得する
3. usr_itemsテーブルにアイテムが追加される（amount=5）
4. ユーザーがスタミナ回復アイテムを使用する（2個消費）
5. スタミナが回復し、usr_itemsのamountが3に減少する
6. log_itemsとlog_staminasにログが記録される

**シナリオ2: スタミナ満タン→アイテム使用失敗→エラー処理**
1. ユーザーのスタミナが満タン（200/200）の状態
2. ユーザーがスタミナ回復アイテムを使用しようとする
3. USER_STAMINA_FULLエラーが返却される
4. アイテムは消費されない（usr_itemsのamountは変化しない）
5. ログは記録されない（トランザクションロールバック）

**シナリオ3: 複数個同時使用でスタミナ上限超過（2025-12-02修正）**
1. ユーザーのスタミナが50/180の状態
2. 1個あたりの回復量は90（180 × 50%）
3. ユーザーがスタミナ回復アイテムを2個同時使用する
4. 回復量計算: 90 × 2 = 180
5. **スタミナは230になる**（50 + 180 = 230、スタミナ上限180を超えてもOK）
6. アイテムは2個消費される
7. ログには実際の加算量（180）が記録される
8. **3個目は使用不可**: 現在スタミナ(230) > スタミナ上限(180) なので追加使用不可

**シナリオ4: スタミナ上限を超えた後は使用不可（正確な仕様）**
1. ユーザーのスタミナが200/180の状態（以前の使用でスタミナ上限を超えている）
2. 現在スタミナ(200) > スタミナ上限(180) なので**使用不可**
3. クライアント側で「スタミナが上限を超えています」等のメッセージを表示
4. サーバー側でもUSER_STAMINA_FULLエラーを返す

**シナリオ5: スタミナ上限ちょうどの場合は使用不可**
1. ユーザーのスタミナが180/180の状態（スタミナ上限ちょうど）
2. 現在スタミナ(180) >= スタミナ上限(180) なので**使用不可**
3. クライアント側で使用ボタンをグレーアウト

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [ ] なし（既存テーブルをそのまま利用）

### 6.2 マイグレーション実行順序

マイグレーションは不要です。

### 6.3 ロールバック方針

DB変更がないため、ロールバック対応は不要です。

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-schema-reference`: glow-schema YAMLとの整合性確認（ItemType定義追加時）
- `domain-layer`: ドメインレイヤーの実装パターン参照（ItemService拡張時）
- `api-test-implementation`: テストコードの実装（ユニットテスト・機能テスト作成時）
- `sail-check-fixer`: コード品質チェックとエラー修正（実装完了後）

**使用タイミング:**
1. **glow-schema変更時**: `api-schema-reference` スキルでItemType定義追加
2. **ItemService実装時**: `domain-layer` スキルでドメイン設計パターンを参照
3. **テスト実装時**: `api-test-implementation` スキルでテストコード作成
4. **実装完了時**: `sail-check-fixer` スキルで品質チェック実施

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目:** スタミナ増加ログの記録方法

- **選択肢A: UserService::addStamina()内でログ記録**
  - メリット: 一箇所に集約、他の箇所でaddStamina()を使ってもログが残る
  - デメリット: UserService::addStamina()の既存実装を変更する必要がある

- **選択肢B: ItemService::applyStaminaRecovery()内でログ記録**
  - メリット: 既存実装への影響が少ない、スタミナ回復アイテム専用のログ記録が可能
  - デメリット: ログ記録が分散する、他の箇所でaddStamina()を使った場合はログが残らない

- **推奨: 選択肢Aを推奨**
  - 理由: UserService::addStamina()は他の箇所でも使われる可能性があるため、ログ記録を一元化するべき
  - 既存実装の確認を行い、addStamina()内でログ記録が行われていない場合は追加する

### 8.2 仕様の解釈

**項目:** システム上限超過時のエラーハンドリング

- **解釈A: エラーを返す（ErrorCode::USER_STAMINA_EXCEEDS_LIMIT）**
  - この解釈の場合: クライアント側の制御に不備がある場合にエラーとして検出できる

- **解釈B: エラーを返さず999で打ち切る**
  - この解釈の場合: ユーザー体験を優先し、可能な限りアイテムを使用できるようにする

- **確認先:** プランナー（ゲーム企画担当者）
- **暫定対応:** 解釈Bを採用（サーバーAPI要件書Q2の回答に基づく）

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**
1. `api/app/Domain/User/Services/UserBuyStaminaService.php`
   - スタミナ購入処理（広告視聴、ダイヤモンド消費）
   - calcAddStamina()メソッドの計算ロジックを参考にする

2. `api/app/Domain/Item/Services/ItemService.php`
   - アイテム種別別処理の実装パターン
   - applyRandomFragmentBox()、applyCharacterFragment()の構造を参考にする

3. `api/app/Domain/Item/Services/UsrItemService.php`
   - アイテム所持数管理、消費処理
   - consumeItem()、addItemByRewards()の実装を参考にする

**参考になるドメイン設計:**
- `Domain\Item`: アイテム管理ドメイン
- `Domain\User`: ユーザー管理ドメイン（スタミナ管理含む）
- `Domain\Resource`: リソース管理ドメイン（報酬配布、ログ記録）

**参考になるDB設計:**
- `usr_items`: アイテム所持数管理テーブル
- `log_items`: アイテム消費・獲得ログ
- `log_staminas`: スタミナ増減ログ

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
  - DB接頭辞付き変数命名（`$mstItemId`, `$usrItem`等）
  - return array禁止ルール

- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み
  - mst_itemsテーブルへの新規アイテム追加後の配信処理

### 9.3 実装時のTips

**よくあるハマりポイント:**
1. **DB接頭辞の忘れ**: 変数名に`mst`, `usr`, `log`等の接頭辞を必ずつける
2. **return arrayの使用**: Entityまたはinterfaceで返却する（配列禁止）
3. **トランザクション境界**: UseCase層でトランザクション開始、Service層では開始しない
4. **ログ記録のタイミング**: アイテム消費とスタミナ加算の両方が成功した時点でログ記録

**パフォーマンスチューニングのポイント:**
1. **N+1問題の回避**: UsrItemRepository::getListByMstItemIds()でバルク取得
2. **キャッシュの活用**: UsrItemRepositoryはキャッシュ機構を持つため、syncModel()で自動更新
3. **インデックスの活用**: usr_itemsテーブルのPRIMARY KEY (usr_user_id, mst_item_id)

**デバッグのコツ:**
1. **ログ確認**: log_itemsとlog_staminasを確認してアイテム消費とスタミナ加算が記録されているか確認
2. **トランザクションの確認**: エラー時にロールバックされているか、usr_itemsとusr_user_parametersの状態を確認
3. **sail checkの活用**: phpstan、phpcbf、deptracでコード品質を早期にチェック

---

## 10. まとめ

本実装設計書は、スタミナ回復アイテム機能のサーバーAPI実装における具体的な設計指針を提供します。

**重要なポイント:**
1. 既存実装を最大限活用し、新規コンポーネントは最小限とする
2. ItemType定義追加により、既存のアイテム管理システムに統合
3. スタミナ回復計算は既存のUserBuyStaminaServiceのロジックを流用
4. 報酬配布、ログ記録、ミッション連携は既存の仕組みをそのまま利用
5. 段階的実装（ItemType追加 → ドメインロジック実装 → テスト）により、リスクを最小化

**実装開始前の確認事項:**
- [ ] glow-schemaリポジトリへのアクセス権限
- [ ] ローカル環境のDocker起動確認
- [ ] sail checkコマンドの動作確認
- [ ] 既存のItemServiceTest、UserBuyStaminaServiceTestの実行確認

実装時は、本設計書を参照しつつ、既存のコーディング規約とアーキテクチャパターンに従って実装してください。
