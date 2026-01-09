# glow-server API実装 コーディング規約

本ドキュメントは、glow-server API実装における統一的なコーディング規約をまとめたものです。

既存コードベースから網羅的に抽出したパターンとルールに基づいています。

## 目次

1. [基本原則](#基本原則)
2. [命名規則](#命名規則)
3. [アーキテクチャと層の責務](#アーキテクチャと層の責務)
4. [Controller層](#controller層)
5. [Domain層](#domain層)
6. [Response層](#response層)
7. [データベース層](#データベース層)
8. [テスト実装](#テスト実装)
9. [コード品質管理](#コード品質管理)

---

## 基本原則

### 絶対ルール

#### 1. return array は禁止

**❌ 禁止パターン:**
```php
public function getUserData(string $userId): array
{
    return [
        'name' => $name,
        'level' => $level,
    ];
}
```

**✅ 推奨パターン:**
```php
public function getUserData(string $userId): UserData
{
    return new UserData(
        name: $name,
        level: $level,
    );
}
```

**理由:**
- 型安全性の確保
- IDEの補完が効く
- リファクタリング時の影響範囲が明確
- 将来のメンテナンスコストを削減

**例外:**
- ResponseFactory内での段階的な配列構築のみ許容

#### 2. DB接頭辞による変数命名

データベーステーブルの種類を変数名で明示すること：

```php
// ✅ 正しい命名
$mstUnitId = $validated['mstUnitId'];          // mst_units テーブル
$usrUnit = $this->usrUnitRepository->find($id); // usr_units テーブル
$oprGachaId = $validated['oprGachaId'];        // opr_gacha テーブル
$logData = $this->logRepository->create(...);  // log_* テーブル
```

**理由:**
- データの出所が一目で分かる
- テーブル間の関連が明確になる
- バグの早期発見につながる

#### 3. Domain EntityとEloquent Modelの使い分け

```php
// ✅ Delegatorの戻り値はEntity
public function getUserProfile(string $userId): UsrUserProfileEntity
{
    return $this->repository->find($userId)->toEntity();
}

// ✅ Repository内部ではModel
public function find(string $userId): UsrUserProfile
{
    return UsrUserProfile::where('usr_user_id', $userId)->first();
}
```

**理由:**
- ドメイン境界を越える時はEntityを使う
- 内部実装（Repository）ではModelを使う
- キャッシュ制御とドメインロジックを分離

---

## 命名規則

### 変数命名

#### DB接頭辞付き変数

| 接頭辞 | 用途 | 例 |
|--------|------|-----|
| `$mst*` | マスターテーブル | `$mstStageId`, `$mstUnitId`, `$mstMissionIds` |
| `$opr*` | オペレーションテーブル | `$oprGachaId`, `$oprProductId` |
| `$usr*` | ユーザーテーブル | `$usrUnit`, `$usrUnitId`, `$usrItems` |
| `$log*` | ログテーブル | `$logBankRegistered` |
| `$mng*` | 管理テーブル | `$mngRelease` |
| `$adm*` | 管理用テーブル | `$admUser` |

#### 標準的なローカル変数名

```php
// リクエスト処理
$validated = $this->request->validate([...]);
$user = $this->request->user();
$platform = (int) $this->request->header(System::HEADER_PLATFORM);
$billingPlatform = $request->getBillingPlatform();

// UseCase実行結果
$resultData = $useCase->exec(...);

// 現在時刻
$now = $this->clock->now();

// Collection
$usrUnits = $this->usrUnitRepository->getAll($userId);
$mstStages = $this->mstStageRepository->getActiveStages($now);
```

### メソッド命名

#### Controller メソッド

動詞 + 名詞パターン：

```php
// 取得系
public function prize(...)       // 賞品取得
public function history(...)     // 履歴取得
public function fetch(...)       // データ取得

// 開始系
public function start(...)       // 開始
public function drawAd(...)      // 広告ガシャ
public function linkBnid(...)    // BNID連携

// 終了系
public function end(...)         // 終了
public function cleanup(...)     // クリーンアップ

// 変更系
public function changeName(...)  // 名前変更
public function changeAvatar(...) // アバター変更

// 購入系
public function purchase(...)    // 購入
public function buyStaminaAd(...) // スタミナ購入（広告）

// 戦闘系
public function abort(...)           // 中断
public function continueAd(...)      // コンティニュー（広告）
public function continueDiamond(...) // コンティニュー（ダイヤ）
```

#### Repository メソッド

| パターン | 戻り値 | 説明 |
|----------|--------|------|
| `find*()` | Model\|Entity\|null | 見つからない場合null |
| `get*()` | Model\|Entity | 見つからない場合例外 |
| `getListByXxx()` | Collection | 複数取得 |
| `create()` | Model\|Entity | 新規作成 |
| `update*()` | void | 更新 |
| `delete*()` | void | 削除 |
| `saveModels()` | void | 複数保存（キャッシュ版） |
| `syncModel(s)()` | void | キャッシュ同期 |

```php
// 例
public function findByMstUnitId(string $userId, string $mstUnitId): ?UsrUnitInterface;
public function getById(string $id): UsrUnitInterface;
public function getListByUsrUserId(string $userId): Collection;
public function create(string $userId, string $mstUnitId): UsrUnitInterface;
public function updateLevel(string $id, int $level): void;
public function deleteById(string $id): void;
```

#### Service メソッド

ビジネスロジックの意図を明確に：

```php
// 検証系
public function validateDiamond(string $userId, int $amount): void;
public function checkCheat(array $battleLog, ...): void;

// 取得系
public function getActiveUsrAdventBattles(string $userId, CarbonImmutable $now): Collection;
public function fetchUsrAdventBattleList(string $userId, CarbonImmutable $now): Collection;

// 状態遷移系
public function resetUsrAdventBattle(UsrAdventBattleInterface $model, CarbonImmutable $now): bool;
public function incrementChallengeCount(UsrAdventBattleInterface $model, bool $isChallengeAd): void;

// 計算系
public function calculateRewards(...): Collection;
public function computeDamage(...): int;
```

#### UseCase メソッド

```php
// 標準的な実行メソッド
public function exec(...): ResultData;

// 引数パターン
public function exec(
    CurrentUser $user,        // ユーザー情報（必須）
    string $mstStageId,       // リクエストパラメータ
    int $partyNo,
    bool $isChallengeAd,
    array $inGameBattleLog,
): StageStartResultData;
```

### クラス命名

#### Domain層

```php
// Entity
class Clock { }
class CurrentUser { }
class UsrUserEntity { }
class UsrUnitEntity { }

// Model
class UsrUser extends UsrEloquentModel { }
class UsrUnit extends UsrModel { }

// Repository
class UsrUserRepository { }
class UsrUnitRepository extends UsrModelCacheRepository { }

// Service
class AdventBattleService { }
class UserDeviceService { }

// UseCase
class SignUpUseCase { }
class StageStartUseCase { }

// Delegator
class AuthDelegator { }
class CurrencyDelegator { }
```

#### Response層

```php
// ResponseFactory
class UserResponseFactory { }
class GachaResponseFactory { }

// ResponseDataFactory
class ResponseDataFactory { }

// ResultData（UseCaseの戻り値）
class UserInfoResultData { }
class StageEndResultData { }

// Data（レスポンス用データクラス）
class UsrParameterData { }
class GameBadgeData { }
```

---

## アーキテクチャと層の責務

### 全体構造

```
┌─────────────────────────────────────────────────┐
│               Controller                         │
│  - リクエスト受け取り                            │
│  - バリデーション                                │
│  - UseCase呼び出し                              │
│  - レスポンス返却                                │
└─────────────────┬───────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────┐
│               UseCase                            │
│  - トランザクション管理                          │
│  - ビジネスロジックのオーケストレーション        │
│  - Delegator経由で他ドメイン操作                │
└─────────────────┬───────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────┐
│               Service                            │
│  - ビジネスロジック実装                          │
│  - トランザクション不要な処理                    │
│  - Repository呼び出し                           │
└─────────────────┬───────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────┐
│             Repository                           │
│  - データアクセス層                              │
│  - CRUD操作                                     │
│  - キャッシュ制御                                │
└─────────────────┬───────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────┐
│          Model / Entity                          │
│  - Model: DB操作                                │
│  - Entity: ドメイン層データ                      │
└─────────────────────────────────────────────────┘
```

### Delegator パターン

他ドメインへのインターフェース（Facade）：

```php
class AuthDelegator
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private IdTokenService $idTokenService,
        private UserDeviceService $userDeviceService,
        private UsrDeviceRepository $usrDeviceRepository,
    ) {}

    // Entity で返却（Model ではない）
    public function createUserDevice(
        string $usrUserId,
        ?string $uuid = null,
        ?string $bnidLinkedAt = null,
        string $osPlatform = ''
    ): UsrDeviceEntity {
        return $this->usrDeviceRepository->create(
            $usrUserId, $uuid, $bnidLinkedAt, $osPlatform
        )->toEntity();
    }

    // Collection<Entity> で返却
    public function getUsrDevices(string $usrUserId): Collection {
        return $this->usrDeviceRepository
            ->getByUsrUserId($usrUserId)
            ->map(fn($record) => $record->toEntity());
    }
}
```

**Delegator の責務:**
- 内部実装（Service/Repository）を隠蔽
- 他ドメインに公開するAPIのみを提供
- 返却型はEntityまたはCollection<Entity>
- Modelを返却しない

---

## Controller層

### 基本構造

```php
class GachaController extends Controller
{
    // (1) コンストラクタでの依存性注入
    public function __construct(
        private Request $request,
        private GachaResponseFactory $responseFactory
    ) {}

    // (2) 各アクションメソッド
    public function draw(GachaDrawUseCase $useCase): JsonResponse
    {
        // (2-1) バリデーション
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
        ]);

        // (2-2) ヘッダ/リクエストから情報取得
        $platform = (int)$this->request->header(System::HEADER_PLATFORM);

        // (2-3) UseCase実行
        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
        );

        // (2-4) レスポンス返却
        return $this->responseFactory->createDrawResponse($resultData);
    }
}
```

### バリデーション

#### 基本的なバリデーション

```php
$validated = $this->request->validate([
    'oprGachaId' => 'required',
    'drewCount' => 'required|integer|min:1',
    'name' => 'required|string|max:20',
]);
```

#### 複合バリデーション

```php
$validated = $request->validate([
    'birthDate' => 'required|integer|date_format:Ymd',
    'email' => 'required|email',
    'age' => 'required|integer|between:1,150',
]);
```

#### nullable フィールド

```php
$validated = $this->request->validate([
    'mstEmblemId' => 'present',  // nullable な場合は present を使用
    'name' => 'nullable|string',
]);
```

### リクエストパラメータ取得

#### ヘッダから取得

```php
// 標準
$platform = (int) $request->header(System::HEADER_PLATFORM);
$language = $request->header(System::HEADER_LANGUAGE);
$accessToken = $request->header(System::HEADER_ACCESS_TOKEN, '');

// マクロメソッド（AppServiceProviderで定義）
$platform = $request->getPlatform();
$billingPlatform = $request->getBillingPlatform();
```

#### デフォルト値付き取得

```php
$partyNo = $request->input('partyNo', 0);
$isChallengeAd = $request->input('isChallengeAd', false);
$clientUuid = $request->input('clientUuid', null);
```

### import順序

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Game\Gacha;

// 1. 定数クラス
use App\Domain\Common\Constants\System;
use App\Domain\Common\Constants\ErrorCode;

// 2. Enum クラス
use App\Domain\Common\Enums\Language;
use App\Domain\Gacha\Enums\CostType;

// 3. UseCase クラス（アルファベット順）
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Gacha\UseCases\GachaHistoryUseCase;
use App\Domain\Gacha\UseCases\GachaPrizeUseCase;

// 4. ResponseFactory クラス
use App\Http\ResponseFactories\GachaResponseFactory;

// 5. Illuminate クラス
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
```

---

## Domain層

### ディレクトリ構造

```
api/app/Domain/
├── Common/                  # 全ドメイン共通
│   ├── Constants/          # 定数定義
│   ├── Entities/           # 共通エンティティ
│   ├── Enums/              # 共通Enum
│   ├── Exceptions/         # 例外クラス
│   ├── Services/           # 共通サービス
│   ├── Traits/             # Trait
│   └── Utils/              # ユーティリティ
│
├── Resource/               # リソース共通（DB操作基盤）
│   ├── Usr/               # ユーザーテーブル関連
│   ├── Mst/               # マスターテーブル関連
│   └── Log/               # ログテーブル関連
│
├── Auth/                   # 各ドメイン
├── Unit/
├── Stage/
├── Party/
├── Gacha/
└── ...
```

### 各ドメインの標準構成

```
Domain/{DomainName}/
├── Constants/           # ドメイン固有の定数
├── Delegators/          # 他ドメインへのインターフェース
├── Entities/            # Domain Entity
├── Enums/              # 列挙型
├── Factories/          # エンティティ生成
├── Models/             # Eloquent Model
├── Repositories/       # DataAccessObject
├── Services/           # ビジネスロジック
└── UseCases/           # トランザクション制御
```

### Entity実装パターン

#### シンプルなValue Object

```php
class DateTimeRange
{
    public function __construct(
        public readonly CarbonImmutable $startAt,
        public readonly CarbonImmutable $endAt,
    ) {}
}
```

#### ビジネスロジック含むEntity

```php
/** @immutable */
class CurrentUser
{
    public function __construct(
        public string $id,
        public string $gameStartAt,
        public int $status = UserStatus::NORMAL->value,
        public ?string $suspendEndAt = null,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function isSuspended(CarbonImmutable $now): bool
    {
        if ($this->status !== UserStatus::BAN_TEMPORARY_CHEATING->value &&
            $this->status !== UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value) {
            return false;
        }

        if ($this->suspendEndAt === null) {
            return true;
        }

        return $now->lessThan(CarbonImmutable::parse($this->suspendEndAt));
    }
}
```

#### 複雑な計算ロジックを持つEntity

```php
class Lottery
{
    private int $totalWeight;

    public function __construct(
        private array $lots,              // LotteryContent[]
        private RandomUtil $randomUtil,
    ) {
        $totalWeight = 0;
        foreach ($this->lots as $lot) {
            if ($lot->isValid()) {
                $totalWeight += $lot->getWeight();
            }
        }
        $this->totalWeight = (int) $totalWeight;
    }

    public function draw(?string $seed = null): mixed
    {
        if ($this->totalWeight === 0) {
            return null;
        }

        $random = $this->randomUtil->getRandomInt(1, $this->totalWeight, $seed);

        $accumulatedWeight = 0;
        foreach ($this->lots as $lot) {
            if (!$lot->isValid()) {
                continue;
            }

            $accumulatedWeight += $lot->getWeight();
            if ($random <= $accumulatedWeight) {
                return $lot->getContent();
            }
        }

        return null;
    }
}
```

### Model実装パターン

#### Eloquent Model

```php
class UsrUser extends UsrEloquentModel implements UsrUserInterface
{
    use HasFactory;

    protected $fillable = [
        'game_start_at',
        'tutorial_status',
    ];

    // Getter/Setter
    public function getUsrUserId(): string
    {
        return $this->id;
    }

    public function setTutorialStatus(string $tutorialStatus): void
    {
        $this->tutorial_status = $tutorialStatus;
    }

    // Entity変換
    public function toEntity(): UsrUserEntity
    {
        return new UsrUserEntity(
            $this->getUsrUserId(),
            $this->getBnUserId(),
            $this->hasBnUserId(),
            $this->getTutorialStatus(),
            $this->getGameStartAt(),
            $this->getClientUuid(),
        );
    }
}
```

#### ビジネスロジック含むModel

```php
class UsrAdventBattle extends UsrEloquentModel implements UsrAdventBattleInterface
{
    protected $fillable = [...];

    protected $casts = [
        'is_ranking_reward_received' => 'bool',
        'is_excluded_ranking' => 'bool',
    ];

    // キャッシュキー生成
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_advent_battle_id;
    }

    // 状態遷移
    public function incrementChallengeCount(bool $isChallengeAd): void
    {
        $this->challenge_count++;
        if ($isChallengeAd) {
            $this->reset_ad_challenge_count++;
        } else {
            $this->reset_challenge_count++;
        }
    }

    // ビジネスロジック
    public function isFirstClear(): bool
    {
        return $this->clear_count === 1;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->reset_challenge_count = 0;
        $this->reset_ad_challenge_count = 0;
        $this->latest_reset_at = $now->toDateTimeString();
    }
}
```

### Repository実装パターン

#### キャッシュ対応Repository

```php
class UsrUnitRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrUnit::class;

    // 保存処理（UPSERT）
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrUnitInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_unit_id' => $model->getMstUnitId(),
                'level' => $model->getLevel(),
                'rank' => $model->getRank(),
            ];
        })->toArray();

        UsrUnit::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_unit_id'],  // unique key
            ['level', 'rank'],                // update columns
        );
    }

    // 単一取得（キャッシュ適用）
    public function findByMstUnitId(
        string $usrUserId,
        string $mstUnitId
    ): ?UsrUnitInterface {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_unit_id',
            $mstUnitId,
            function () use ($usrUserId, $mstUnitId) {
                return UsrUnit::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_unit_id', $mstUnitId)
                    ->first();
            },
        );
    }

    // 新規作成
    public function create(
        string $usrUserId,
        string $mstUnitId
    ): UsrUnitInterface {
        $usrUnit = new UsrUnit();
        $usrUnit->usr_user_id = $usrUserId;
        $usrUnit->mst_unit_id = $mstUnitId;
        $usrUnit->level = 1;
        $usrUnit->rank = 0;

        $this->syncModel($usrUnit);
        return $usrUnit;
    }
}
```

#### シンプルなRepository（キャッシュなし）

```php
class UsrDeviceRepository
{
    public function findByUuid(string $uuid): ?UsrDeviceInterface
    {
        return UsrDevice::where('uuid', $uuid)->first();
    }

    public function getByUsrUserId(string $usrUserId): Collection
    {
        return UsrDevice::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
    }

    public function create(
        string $usrUserId,
        ?string $uuid = null,
        ?string $bnidLinkedAt = null,
        string $osPlatform = ''
    ): UsrDeviceInterface {
        $uuid ??= (string) Str::uuid();

        return UsrDevice::create([
            'usr_user_id' => $usrUserId,
            'uuid' => $uuid,
            'bnid_linked_at' => $bnidLinkedAt,
            'os_platform' => $osPlatform,
        ]);
    }

    public function updateBnidLinkedAt(
        string $id,
        string $usrUserId,
        CarbonImmutable $now
    ): void {
        UsrDevice::query()
            ->where('id', $id)
            ->where('usr_user_id', $usrUserId)
            ->update(['bnid_linked_at' => $now->toDateTimeString()]);
    }
}
```

### Service実装パターン

Service は**トランザクション不要な処理**を担当：

```php
class UserDeviceService
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private IdTokenService $idTokenService,
        private UsrDeviceRepository $usrDeviceRepository,
    ) {}

    public function findByIdToken(string $idToken): ?UsrDeviceInterface
    {
        $uuid = $this->idTokenService->getUuid($idToken);
        return $this->usrDeviceRepository->findByUuid($uuid);
    }

    public function linkBnidByAccessToken(
        string $accessToken,
        CarbonImmutable $now
    ): string {
        $accessTokenUser = $this->accessTokenService->findUser($accessToken);
        if ($accessTokenUser === null) {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }

        $this->usrDeviceRepository->updateBnidLinkedAt(
            $accessTokenUser->getDeviceId(),
            $accessTokenUser->getUsrUserId(),
            $now
        );

        return $accessTokenUser->getDeviceId();
    }
}
```

**パターン:**
- コンストラクタで依存注入
- Repository操作を直接呼び出し
- トランザクション制御なし
- 他のServiceを呼び出す場合あり

### UseCase実装パターン

UseCase は**トランザクション管理が必要な処理**を担当：

```php
class SignUpUseCase
{
    use UseCaseTrait;  // トランザクション処理用

    public function __construct(
        private UsrModelManager $usrModelManager,
        private Clock $clock,
        // Repository
        private MstUserLevelRepository $mstUserLevelRepository,
        private UsrUserRepository $usrUserRepository,
        // Delegator
        private CurrencyDelegator $currencyDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
    ) {}

    public function exec(
        string $platform,
        string $billingPlatform,
        ?string $clientUuid
    ): array {
        $now = $this->clock->now();

        try {
            // ユーザー作成
            $user = $this->usrUserRepository->make($now, $clientUuid);
            $this->usrModelManager->setUsrUserId($user->getId());
            $this->usrUserRepository->syncModel($user);

            // ユーザー初期化
            $mstUserLevel = $this->mstUserLevelRepository->getByLevel(1, true);
            $this->usrUserParameterRepository->create(
                $user->getId(),
                $mstUserLevel->getStamina(),
                $now
            );

            // Delegator経由でドメイン初期化
            $this->userDelegator->createUsrUserProfile($user->getId());
            $this->outpostDelegator->registerInitialOutpost($user->getId());

        } catch (\Exception $e) {
            throw new GameException(ErrorCode::USER_CREATE_FAILED, $e->getMessage());
        }

        // トランザクション実行
        list($usrCurrencySummary, $idToken) = $this->applyUserTransactionChanges(
            function () use ($user, $now, $platform, $billingPlatform) {
                // Device作成
                $userDevice = $this->usrDeviceRepository->create(
                    $user->getId(),
                    osPlatform: $platform
                );
                $idToken = $this->idTokenService->create($userDevice->getUuid());

                // 通貨初期化
                $osPlatform = $this->appCurrencyDelegator->getOsPlatform($platform);
                $usrCurrencySummary = $this->currencyDelegator->createUser(
                    userId: $user->getId(),
                    osPlatform: $osPlatform,
                    billingPlatform: $billingPlatform,
                    freeAmount: 0,
                );

                return [$usrCurrencySummary, $idToken];
            },
        );

        return [
            'id_token' => $idToken,
            'currency_summary' => $usrCurrencySummary
        ];
    }
}
```

**パターン:**
- UseCaseTrait を use
- exec() メソッドで処理開始
- applyUserTransactionChanges() でトランザクション管理
- コールバック内で他ドメイン操作
- Delegator経由でドメイン操作

---

## Response層

### アーキテクチャ

```
Controller → ResponseFactory → ResponseDataFactory → JSON
      ↓
   ResultData
```

### ResponseFactory 実装

```php
class UserResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {}

    public function createInfoResponse(
        UserInfoResultData $resultData
    ): JsonResponse {
        $result = [];

        // 段階的にデータを追加
        $result = $this->responseDataFactory->addMyIdData(
            $result,
            $resultData->usrUserProfile
        );
        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrUserParameter
        );

        return response()->json($result);
    }
}
```

### ResponseDataFactory 実装

```php
class ResponseDataFactory
{
    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrParameterData(
        array $result,
        UsrParameterData $usrUserParameter
    ): array {
        // glow-schemaのYAML定義に基づいたキー名（camelCase）
        $result['usrParameter'] = [
            'level' => $usrUserParameter->getLevel(),
            'exp' => $usrUserParameter->getExp(),
            'coin' => $usrUserParameter->getCoin(),
            'stamina' => $usrUserParameter->getStamina(),
            // 日時データは必ずISO8601に変換
            'staminaUpdatedAt' => StringUtil::convertToISO8601(
                $usrUserParameter->getStaminaUpdatedAt()
            ),
            'freeDiamond' => $usrUserParameter->getFreeDiamond(),
            'paidDiamondIos' => $usrUserParameter->getPaidDiamondIos(),
            'paidDiamondAndroid' => $usrUserParameter->getPaidDiamondAndroid(),
        ];
        return $result;
    }
}
```

**重要ルール:**
1. 配列キーは**camelCase**（glow-schema定義に従う）
2. 日時データは必ず**StringUtil::convertToISO8601()**で変換
3. メソッド名は`add{EntityName}Data`形式
4. 配列を受け取り、配列を返す（メソッドチェーン可能）

### ResultData クラス

```php
class UserInfoResultData
{
    public function __construct(
        public UsrUserProfileInterface $usrUserProfile,
        public UsrParameterData $usrUserParameter,
    ) {}
}

class StageEndResultData
{
    public function __construct(
        public UserLevelUpData $userLevelUpData,
        public Collection $stageAlwaysClearRewards,
        public Collection $stageRandomClearRewards,
        public Collection $stageFirstClearRewards,
        public Collection $usrConditionPacks,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $newUsrEnemyDiscoveries,
        public Collection $oprCampaignIds,
    ) {}
}
```

### Data クラス

```php
class UsrParameterData
{
    public function __construct(
        private int $level,
        private int $exp,
        private int $coin,
        private int $stamina,
        private ?string $staminaUpdatedAt,
        private int $freeDiamond,
        private int $paidDiamondIos,
        private int $paidDiamondAndroid,
    ) {}

    public function getLevel(): int
    {
        return $this->level;
    }

    // 他のgetterメソッド...
}
```

### 日時変換

```php
// StringUtil.php
public static function convertToISO8601(?string $dateString): ?string
{
    if (is_null($dateString) || $dateString === '') {
        return $dateString;
    }

    // DB形式: 'Y-m-d H:i:s'
    $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    // ISO8601形式: '2025-01-20T10:30:45+00:00'
    return $dateTime->format(\DateTime::ATOM);
}
```

---

## データベース層

### Database接続設定

```php
class Database
{
    public const MST_CONNECTION = 'mst';      // Master DB (MySQL)
    public const OPR_CONNECTION = 'opr';      // Operation DB (MySQL)
    public const MNG_CONNECTION = 'mng';      // Management DB (MySQL)
    public const TIDB_CONNECTION = 'tidb';    // User/Log/Sys DB (TiDB)
}

// Modelでの使用
class UsrUser extends UsrEloquentModel
{
    protected $connection = Database::TIDB_CONNECTION;
}

class MstUnit extends MstModel
{
    protected $connection = Database::MST_CONNECTION;
}
```

### Model実装パターン

#### 基底Model（UsrEloquentModel）

```php
abstract class UsrEloquentModel extends BaseModel implements UsrModelInterface
{
    use BaseHasUuids;
    use HasFactory;

    protected $connection = Database::TIDB_CONNECTION;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dateFormat = 'Y-m-d H:i:s.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->id)) {
            $this->id = $this->newUniqueId();
        }
    }

    // マジックメソッド: get{PropertyName}() で自動取得
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'get')) {
            $property = lcfirst(substr($method, 3));
            $property = \Illuminate\Support\Str::snake($property);

            if (array_key_exists($property, $this->attributes)) {
                return $this->attributes[$property];
            }
        }
        return parent::__call($method, $parameters);
    }
}
```

**重要機能:**
- UUID主キー（`$keyType = 'string'`, `$incrementing = false`）
- マジックメソッド対応（`getMstUnitId()`等が自動定義）
- 日付フォーマット制御（`Y-m-d H:i:s.`）

### プライマリキーと命名規則

```php
// 単一プライマリキー
class UsrUserProfile extends UsrEloquentModel
{
    protected $primaryKey = 'usr_user_id';
    protected $fillable = ['usr_user_id', 'name'];
}

// 複合プライマリキー（非EloquentModel）
class UsrUnit extends UsrModel
{
    protected static string $tableName = 'usr_units';
    protected array $modelKeyColumns = ['usr_user_id', 'mst_unit_id'];
}
```

**テーブル名対応:**
| Model | Table | DB |
|-------|-------|-----|
| UsrUser | usr_users | usr (TiDB) |
| UsrUnit | usr_units | usr (TiDB) |
| MstUnit | mst_units | mst (MySQL) |
| LogUnitLevelUp | log_unit_level_ups | log (TiDB) |

### タイムスタンプ

```php
// Usr/Log DB: created_at, updated_at を使用
class UsrUser extends UsrEloquentModel
{
    // protected $timestamps = true; (デフォルト)
}

// Master DB: タイムスタンプ不使用
class MstUnit extends MstModel
{
    public $timestamps = false;
}
```

### トランザクション処理

```php
// UseCaseTrait
trait UseCaseTrait
{
    use UsrModelManagerTrait;

    private function transaction(
        callable $callback,
        array $connections = [],
    ): mixed {
        if (count($connections) === 0) {
            $connections = [config('database.default')];
        }

        // トランザクション開始
        foreach ($connections as $connection) {
            DB::connection($connection)->beginTransaction();
        }

        try {
            $result = $callback();
            foreach ($connections as $connection) {
                DB::connection($connection)->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            // ロールバック
            foreach ($connections as $connection) {
                DB::connection($connection)->rollBack();
            }
            throw $e;
        }
    }

    public function applyUserTransactionChanges(
        ?callable $callback = null,
        array $connections = []
    ): mixed {
        $wrappedCallback = function () use ($callback) {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback();
            }

            // ミッション進捗更新
            $this->updateMissionProgresses();
            // アクセス日時更新
            $this->updateHourlyAccessedAtAndCreateBankActiveLog();
            // ユーザデータ一括保存
            $this->saveAll();

            return $result;
        };

        $result = $this->transaction($wrappedCallback, $connections);

        // ログデータ一括保存
        $this->saveAllLog();

        return $result;
    }
}
```

**使用例:**

```php
public function exec(...): ResultData
{
    // シンプルなトランザクション
    $result = $this->transaction(
        function () use (...) {
            // ビジネスロジック
        }
    );

    // ユーザデータ変更を含むトランザクション
    return $this->applyUserTransactionChanges(
        function () use (...) {
            // ビジネスロジック
            $this->currencyDelegator->consumeDiamond(...);
            $this->rewardDelegator->sendRewards(...);
        }
    );
}
```

### エラーハンドリング

```php
// GameException
class GameException extends \Exception
{
    public function __construct(
        int $code,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

// 使用例
if ($model === null) {
    throw new GameException(
        ErrorCode::UNIT_NOT_FOUND,
        "usr_unit not found. (usr_user_id: $usrUserId, id: $id)"
    );
}

// try-catch
try {
    $storeReceipt = $this->billingDelegator->verifyReceipt(
        $billingPlatform,
        $productId,
        $receipt
    );
} catch (WpBillingDuplicateReceiptException $e) {
    throw new GameException(
        ErrorCode::BILLING_VERIFY_RECEIPT_DUPLICATE_RECEIPT,
        $e->getMessage(),
        $e
    );
}
```

---

## テスト実装

### ディレクトリ構造

```
api/tests/
├── Unit/                # ユニットテスト（外部依存少）
│   ├── Auth/
│   ├── Domain/
│   └── Shop/Services/
├── Feature/             # フィーチャーテスト（統合テスト）
│   ├── Http/Controllers/
│   ├── Domain/
│   └── Scenario/        # エンドツーエンドシナリオ
├── Support/
│   ├── Traits/          # テスト支援トレイト
│   └── Entities/
├── TestCase.php         # 基底テストクラス
└── CreatesApplication.php
```

### ファイル命名規則

```
{TestedClass}Test.php

例:
- SignUpUseCaseTest.php
- UnitServiceTest.php
- GachaControllerTest.php
```

### テストメソッド命名

```php
// test_ プリフィックス + スネークケース + 日本語も許容
public function test_exec_正常実行_メインパート()
public function test_convertDuplicatedUnitToItem_重複所持しているユニット報酬を別リソースへ変換できる()
public function test_execute_when_user_not_found_throws_exception()
```

### 基底クラスの使い分け

```php
// Unit Test (Mock多用)
class SignUpUseCaseTest extends TestCase
{
}

// Feature Test (API, Controller)
class TutorialLoginBonusScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;
    protected string $baseUrl = '/api/';
}

// Unit Test (外部依存なし)
class DeferredTaskServiceTest extends PHPUnit\Framework\TestCase
{
}
```

### setUp/tearDown

```php
protected function setUp(): void
{
    parent::setUp();

    // ユーザーID生成
    $usrUserId = fake()->uuid();
    $this->setUsrUserId($usrUserId);

    // サービス初期化
    $this->missionUpdateHandleService = app(MissionUpdateHandleService::class);

    // モック設定
    $this->mockDebugForDevelopService();
    $this->mockMngCacheRepository();

    // 環境設定
    Config::set('wp_currency.store.separate_currency_limit_check', false);
}

protected function tearDown(): void
{
    Redis::connection()->flushall();
    parent::tearDown();
}
```

### Arrange-Act-Assert パターン

```php
public function test_exec_正常動作()
{
    // ===== Arrange =====
    $this->fixTime('2025-04-01 00:00:00');
    $platform = System::PLATFORM_ANDROID;
    $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
    $clientUuid = fake()->uuid();

    MstUserLevel::factory()->create([
        'level' => 1,
        'stamina' => 10
    ])->toEntity();

    /** @var SignUpUseCase $useCase */
    $useCase = app()->make(SignUpUseCase::class);

    // ===== Act =====
    $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

    // ===== Assert =====
    $this->assertArrayHasKey('id_token', $result);
    $usrUserId = $result['currency_summary']->getUserId();
    $this->checkBankLogByEventId(
        $usrUserId,
        BankKPIF001EventId::USER_REGISTERED->value
    );
}
```

### Factory の使い方

```php
// 単一レコード作成
MstUserLevel::factory()->create([
    'level' => 1,
    'stamina' => 10,
]);

// 複数レコード作成
MstMissionDailyBonus::factory()->createMany([
    [
        'id' => 'dailyBonus_1',
        'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS,
        'login_day_count' => 1,
    ],
    [
        'id' => 'dailyBonus_2',
        'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS,
        'login_day_count' => 2,
    ],
]);

// make()で保存しない
UsrUser::factory()->make(['id' => '1']);
```

### Mockery の使い方

```php
// 完全モック
$clock = \Mockery::mock(Clock::class);
$clock->shouldReceive('now')->andReturn($now);

$useCase = new SignUpUseCase($this->usrModelManager, $clock, ...);

// Partial Mock
$mockRepository = \Mockery::mock(UsrUserProfileRepository::class)
    ->makePartial();
$mockRepository->shouldAllowMockingProtectedMethods();
$mockRepository->shouldReceive('makeMyIdNumString')
    ->andReturn('1000000001');

$this->app->instance(UsrUserProfileRepository::class, $mockRepository);
```

### アサーション

```php
// 基本
$this->assertEquals($expected, $actual);
$this->assertNotNull($value);
$this->assertTrue($condition);
$this->assertCount($expectedCount, $collection);
$this->assertArrayHasKey('key', $array);

// レスポンス
$response = $this->postJson($this->baseUrl . 'sign_up', [...]);
$response->assertStatus(HttpStatusCode::SUCCESS);
$this->assertArrayHasKey('id_token', $response->json());

// DB
$usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
$this->assertNotNull($usrUserLogin);
$this->assertEquals(1, $usrUserLogin->getLoginCount());
```

### テストの独立性確保

```php
// 1. テストごとに新規ユーザーID
$usrUserId = fake()->uuid();
$this->setUsrUserId($usrUserId);

// 2. RefreshDatabase
protected array $connectionsToTransact = [
    Database::MST_CONNECTION,
    Database::MNG_CONNECTION,
    Database::TIDB_CONNECTION,
    Database::ADMIN_CONNECTION,
];

// 3. 時刻の固定
protected function fixTime(?string $dateTime = null): CarbonImmutable
{
    $dateTime = CarbonImmutable::parse($dateTime);
    CarbonImmutable::setTestNow($dateTime);
    \Illuminate\Support\Carbon::setTestNow($dateTime);
    return $dateTime;
}
```

### DataProvider

```php
#[DataProvider('params_test_exec_正常実行_メインパート')]
public function test_exec_正常実行_メインパート(
    string $beforeTutorialStatus,
    string $afterTutorialStatus,
    ?int $errorCode
) {
    // テスト実装
}

public static function params_test_exec_正常実行_メインパート()
{
    return [
        '成功 チュートリアル未プレイ状態から、1つ目完了' => [
            'beforeTutorialStatus' => '',
            'afterTutorialStatus' => 'tutorialContent1',
            'errorCode' => null,
        ],
        '成功 1つ目完了済み状態から、2つ目完了' => [
            'beforeTutorialStatus' => 'tutorialContent1',
            'afterTutorialStatus' => 'tutorialContent1,tutorialContent2',
            'errorCode' => null,
        ],
    ];
}
```

---

## コード品質管理

### PHPStan（静的解析）

```bash
# 実行
sail phpstan

# エラー対処
- 型エラー: 型ヒントを追加
- 未定義変数: 初期化を追加
- メソッド呼び出しエラー: インターフェースを確認
```

### PHPCS/PHPCBF（コーディング規約）

```bash
# 実行
sail phpcs
sail phpcbf

# 自動修正
sail phpcbf
```

### Deptrac（アーキテクチャ検証）

```bash
# 実行
sail deptrac

# エラー対処
- レイヤー違反: 依存関係を修正
- 不正な依存: Delegatorを使用
```

### 統合チェック

```bash
# 全チェック実行
sail check
```

---

## まとめ

このコーディング規約に従うことで、以下が実現されます：

✅ **型安全性**: return array禁止、Entity/Modelの明確な使い分け
✅ **可読性**: DB接頭辞による変数命名、統一的なメソッド命名
✅ **保守性**: 層の責務分離、Delegatorによる疎結合
✅ **テスト性**: Mockery対応、Arrange-Act-Assertパターン
✅ **拡張性**: クリーンアーキテクチャ、ドメイン駆動設計

このドキュメントは既存コードベースから抽出したパターンに基づいています。
新規実装時は、既存の類似機能を参考にしながら、本規約に従って実装してください。
