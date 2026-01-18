# 新しい配布リソースの追加方法

新しい報酬タイプやリソースをRewardSendServiceに追加する際の手順を説明します。

## 目次

- [追加が必要なファイル](#追加が必要なファイル)
- [手順1: RewardTypeの追加](#手順1-rewardtypeの追加)
- [手順2: SendServiceの実装](#手順2-sendserviceの実装)
- [手順3: RewardSendServiceへの登録](#手順3-rewardsendserviceへの登録)
- [手順4: RewardSendPolicyへの追加](#手順4-rewardsendpolicyへの追加)
- [手順5: ログリポジトリの追加（オプション）](#手順5-ログリポジトリの追加オプション)
- [手順6: テストの実装](#手順6-テストの実装)
- [チェックリスト](#チェックリスト)

## 追加が必要なファイル

新しいリソースを追加する際に変更が必要なファイル一覧：

```
api/app/Domain/Resource/Enums/RewardType.php              # 報酬タイプ定義
api/app/Domain/Reward/Services/YourNewSendService.php     # 新しいSendService
api/app/Domain/Reward/Services/RewardSendService.php      # SendServiceマッピング
api/app/Domain/Resource/Entities/RewardSendPolicy.php     # ポリシー設定
api/app/Domain/YourDomain/Repositories/LogYourRepository.php  # ログリポジトリ（オプション）
api/tests/Feature/Domain/Reward/Services/RewardSendServiceTest.php  # テスト
```

## 手順1: RewardTypeの追加

**ファイルパス**: `api/app/Domain/Resource/Enums/RewardType.php`

新しい報酬タイプをenumに追加します。

```php
enum RewardType: string
{
    case COIN = 'coin';
    case FREE_DIAMOND = 'free_diamond';
    case STAMINA = 'stamina';
    case ITEM = 'item';
    case EXP = 'exp';
    case EMBLEM = 'emblem';
    case UNIT = 'unit';
    case ARTWORK = 'artwork';
    case YOUR_NEW_TYPE = 'your_new_type'; // 追加
}
```

**命名規則**:
- スネークケース（小文字 + アンダースコア）
- 分かりやすく簡潔な名前
- 既存のリソース名との重複を避ける

## 手順2: SendServiceの実装

**ファイルパス**: `api/app/Domain/Reward/Services/YourNewSendService.php`

新しいSendServiceクラスを作成します。

### パターン1: シンプルパターン（推奨）

```php
<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\YourDomain\Delegators\YourDelegator;

/**
 * YOUR_NEW_TYPE報酬の配布を担当するサービス
 */
class YourNewSendService implements RewardSendServiceInterface
{
    public function __construct(
        private YourDelegator $yourDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // YourDelegatorに委譲して実際の配布を実行
        $this->yourDelegator->addYourResourceByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
```

### パターン2: メールボックス対応パターン

上限超過時にメールボックスへ送信する場合：

```php
<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\Reward\Traits\RewardSendServiceTrait;
use App\Domain\Resource\Enums\RewardSendMethod;
use App\Domain\YourDomain\Delegators\YourDelegator;

class YourNewSendService implements RewardSendServiceInterface
{
    use RewardSendServiceTrait;

    public function __construct(
        private YourDelegator $yourDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();
        $sendMethod = $context->getSendMethod();

        // 集計
        $totalAmount = 0;
        foreach ($rewards as $reward) {
            $totalAmount += $reward->getAmount();
        }

        if ($totalAmount <= 0) {
            $this->markRewardsAsSent($rewards);
            return new RewardSent($rewards);
        }

        // 送信処理
        $sendCallback = function () use ($usrUserId, $totalAmount, $rewards, $now) {
            $this->yourDelegator->addYourResource(
                $usrUserId,
                $totalAmount,
                $now,
            );
        };

        // RewardSendMethodに応じて処理を分岐
        switch ($sendMethod) {
            case RewardSendMethod::SEND_TO_MESSAGE:
                // 上限超過時はメールボックスへ
                $this->trySendRewardsOrMarkAsSentToMessage(
                    $rewards,
                    $sendCallback,
                );
                break;
            default:
                // 通常送信
                $sendCallback();
                $this->markRewardsAsSent($rewards);
                break;
        }

        return new RewardSent($rewards);
    }
}
```

### パターン3: 追加報酬パターン

配布により別の報酬が発生する場合（経験値→レベルアップ報酬など）：

```php
class YourNewSendService implements RewardSendServiceInterface
{
    public function __construct(
        private RewardManagerInterface $rewardManager,
        private YourDelegator $yourDelegator,
    ) {}

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // 配布実行
        $additionalRewards = $this->yourDelegator->addYourResourceWithRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        // 追加報酬をRewardManagerに追加（重要！）
        if ($additionalRewards->isNotEmpty()) {
            $this->rewardManager->addRewards($additionalRewards);
        }

        return new RewardSent($rewards);
    }
}
```

## 手順3: RewardSendServiceへの登録

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php`

`$sendServiceClasses`配列に新しいSendServiceを登録します。

```php
private array $sendServiceClasses = [
    RewardType::COIN->value => CoinSendService::class,
    RewardType::FREE_DIAMOND->value => FreeDiamondSendService::class,
    RewardType::STAMINA->value => StaminaSendService::class,
    RewardType::ITEM->value => ItemSendService::class,
    RewardType::EXP->value => ExpSendService::class,
    RewardType::EMBLEM->value => EmblemSendService::class,
    RewardType::UNIT->value => UnitSendService::class,
    RewardType::ARTWORK->value => ArtworkSendService::class,
    RewardType::YOUR_NEW_TYPE->value => YourNewSendService::class, // 追加
];
```

**重要**: 配列のキーは`RewardType::YOUR_NEW_TYPE->value`を使用してください。

## 手順4: RewardSendPolicyへの追加

**ファイルパス**: `api/app/Domain/Resource/Entities/RewardSendPolicy.php`

### 4-1. コンストラクタに追加

```php
public function __construct(
    private RewardSendMethod $coinSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $freeDiamondSendMethod = RewardSendMethod::SEND_TO_MESSAGE,
    private RewardSendMethod $staminaSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $itemSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $expSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $emblemSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $unitSendMethod = RewardSendMethod::NONE,
    private RewardSendMethod $yourNewTypeSendMethod = RewardSendMethod::NONE, // 追加
    private ?GameException $resourceLimitReachedException = null,
) {}
```

### 4-2. getSendMethodByRewardTypeに追加

```php
public function getSendMethodByRewardType(string $rewardType): RewardSendMethod
{
    return match ($rewardType) {
        RewardType::COIN->value => $this->coinSendMethod,
        RewardType::FREE_DIAMOND->value => $this->freeDiamondSendMethod,
        RewardType::STAMINA->value => $this->staminaSendMethod,
        RewardType::ITEM->value => $this->itemSendMethod,
        RewardType::EXP->value => $this->expSendMethod,
        RewardType::EMBLEM->value => $this->emblemSendMethod,
        RewardType::UNIT->value => $this->unitSendMethod,
        RewardType::YOUR_NEW_TYPE->value => $this->yourNewTypeSendMethod, // 追加
        default => RewardSendMethod::NONE,
    };
}
```

### 4-3. createThrowErrorWhenResourceLimitReachedPolicyに追加（必要に応じて）

上限超過時にエラーを投げる必要がある場合：

```php
public static function createThrowErrorWhenResourceLimitReachedPolicy(
    GameException $resourceLimitReachedException,
): self {
    return new self(
        coinSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        freeDiamondSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        staminaSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        itemSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        expSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED,
        yourNewTypeSendMethod: RewardSendMethod::THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED, // 追加
        emblemSendMethod: RewardSendMethod::NONE,
        unitSendMethod: RewardSendMethod::NONE,
        resourceLimitReachedException: $resourceLimitReachedException,
    );
}
```

## 手順5: ログリポジトリの追加（オプション）

報酬配布ログを記録する場合、ログリポジトリを作成して登録します。

### 5-1. ログリポジトリの実装

**ファイルパス**: `api/app/Domain/YourDomain/Repositories/LogYourResourceRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\YourDomain\Repositories;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LogYourResourceRepository implements ILogModelRepositoryRewardSend
{
    /**
     * 報酬配布ログを作成
     *
     * @param string $usrUserId
     * @param Collection<BaseReward> $rewards
     * @param CarbonImmutable $now
     */
    public function createByRewards(
        string $usrUserId,
        Collection $rewards,
        CarbonImmutable $now,
    ): void {
        foreach ($rewards as $reward) {
            // ログデータの作成処理
            // 実装は各リソースの要件に応じて異なる
        }
    }
}
```

### 5-2. RewardSendServiceへの登録

**ファイルパス**: `api/app/Domain/Reward/Services/RewardSendService.php`

```php
private array $logRepositoryClasses = [
    RewardType::COIN->value => LogCoinRepository::class,
    RewardType::STAMINA->value => LogStaminaRepository::class,
    RewardType::ITEM->value => LogItemRepository::class,
    RewardType::EXP->value => LogExpRepository::class,
    RewardType::EMBLEM->value => LogEmblemRepository::class,
    RewardType::UNIT->value => LogUnitRepository::class,
    RewardType::YOUR_NEW_TYPE->value => LogYourResourceRepository::class, // 追加
];
```

## 手順6: テストの実装

**ファイルパス**: `api/tests/Feature/Domain/Reward/Services/RewardSendServiceTest.php`

新しいリソースの配布テストを追加します。

```php
public function test_sendRewards_新しいリソースが正常に配布できる(): void
{
    // Setup
    $platform = UserConstant::PLATFORM_IOS;
    $now = $this->fixTime();

    $usrUser = $this->createUsrUser();
    $usrUserId = $usrUser->getUsrUserId();

    // 必要なマスターデータを作成
    MstYourResource::factory()->create(['id' => 'resource_001']);

    // 報酬を設定
    $rewards = collect([
        new Test1Reward(
            RewardType::YOUR_NEW_TYPE->value,
            'resource_001',
            100,
            'test_your_new_type'
        ),
    ]);

    $this->rewardManager->addRewards($rewards);

    // Exercise
    $result = $this->rewardSendService->sendRewards(
        usrUserId: $usrUserId,
        platform: $platform,
        now: $now,
        policy: null
    );
    $this->saveAll();

    // Verify
    $usrYourResource = UsrYourResource::query()
        ->where('usr_user_id', $usrUserId)
        ->where('mst_your_resource_id', 'resource_001')
        ->first();

    $this->assertNotNull($usrYourResource);
    $this->assertEquals(100, $usrYourResource->getAmount());
}
```

## チェックリスト

新しいリソースの追加が完了したら、以下をチェックしてください：

### 必須項目

- [ ] RewardTypeに新しい報酬タイプを追加した
- [ ] SendServiceクラスを実装した
- [ ] RewardSendServiceの`$sendServiceClasses`に登録した
- [ ] RewardSendPolicyのコンストラクタに追加した
- [ ] RewardSendPolicyの`getSendMethodByRewardType`に追加した
- [ ] テストケースを実装した
- [ ] テストが全てパスする

### オプション項目

- [ ] ログリポジトリを実装した（必要な場合）
- [ ] RewardSendServiceの`$logRepositoryClasses`に登録した（必要な場合）
- [ ] `createThrowErrorWhenResourceLimitReachedPolicy`に追加した（上限超過エラーが必要な場合）
- [ ] メールボックス対応を実装した（必要な場合）
- [ ] 追加報酬の仕組みを実装した（必要な場合）

### 動作確認

- [ ] デフォルトポリシーで正常に配布できる
- [ ] エラー投げるポリシーで上限超過時にエラーが発生する（実装した場合）
- [ ] メールボックスへの送信が正常に動作する（実装した場合）
- [ ] ログが正常に記録される（実装した場合）
- [ ] 追加報酬が正常に配布される（実装した場合）

## よくある質問

### Q1: 既存のリソースと同じテーブルを使う場合は？

既存のSendServiceを参考にしてください。例えば、アイテムと同じテーブル構造を使う場合は`ItemSendService`を参考に実装します。

### Q2: 上限超過時の動作をカスタマイズしたい

RewardSendPolicyで個別に設定できます。デフォルトポリシーを変更せず、必要に応じて独自のポリシーを作成してください。

### Q3: 複数のリソースを同時に配布する場合は？

個別のSendServiceは1つの報酬タイプのみを担当します。複数リソースの配布はRewardSendServiceが自動的に処理します。

### Q4: ログ記録は必須？

必須ではありません。要件に応じて実装してください。既存のリソースでもログを記録していないものがあります（FREE_DIAMOND, ARTWORK等）。

## まとめ

- **RewardTypeの追加**: enum定義に追加
- **SendServiceの実装**: 3つのパターンから選択
- **RewardSendServiceへの登録**: マッピング配列に追加
- **RewardSendPolicyへの追加**: コンストラクタとmatchに追加
- **ログリポジトリの追加**: オプション、必要に応じて実装
- **テストの実装**: 必須、正常系と異常系をカバー

詳細な実装パターンは **[guides/reward-types.md](reward-types.md)** を参照してください。
