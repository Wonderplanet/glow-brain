# Gameドメインの実装例

Gameドメインは、どのドメインからも依存しない最上位の基盤ドメインです。ゲームへのログイン、データフェッチなど、ゲーム全体の基盤機能を提供します。

## 特徴

- **他のどのドメインからも依存されない**（最上位レイヤー）
- **他ドメインへDelegatorなしで依存可能**（特権を持つ）
- **構成がシンプル**（Services、UseCasesのみ）
- **Constants、Delegators、Entities、Models、Repositoriesは持たない**

## フォルダ構成

```
api/app/Domain/Game/
├── Services/
│   ├── GameService.php
│   ├── IgnService.php
│   ├── AssetDataManifestService.php
│   └── VersionDataManifestService.php
└── UseCases/
    ├── GameFetchUseCase.php
    ├── GameUpdateAndFetchUseCase.php
    ├── GameVersionUseCase.php
    ├── GameServerTimeUseCase.php
    └── GameBadgeUseCase.php
```

## Service の実装例

Gameドメインは他ドメインのDelegatorを直接DIして使用できます。

**ファイルパス:** `api/app/Domain/Game/Services/GameService.php`（抜粋）

```php
<?php

declare(strict_types=1);

namespace App\Domain\Game\Services;

use App\Domain\Auth\Delegators\AuthDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Domain\Stage\Delegators\StageDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Message\Delegator\MessageDelegator;
use App\Domain\Reward\Delegators\RewardDelegator;
// ... 他の多数のDelegator

use Carbon\CarbonImmutable;

class GameService
{
    public function __construct(
        // 他ドメインのDelegatorを直接DIできる（Game特権）
        private AuthDelegator $authDelegator,
        private UserDelegator $userDelegator,
        private StageDelegator $stageDelegator,
        private ShopDelegator $shopDelegator,
        private MissionDelegator $missionDelegator,
        private MessageDelegator $messageDelegator,
        private RewardDelegator $rewardDelegator,
        // ... 他の多数のDelegator

        // Repositoryも直接DIできる
        private UsrUserParameterRepository $usrUserParameterRepository,
        private UsrStageRepository $usrStageRepository,
        // ...
    ) {
    }

    /**
     * ゲームの更新処理（/api/game/update）
     */
    public function update(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): GameUpdateData {
        // ユーザーパラメータ取得（スタミナ回復含む）
        $usrParameter = $this->userDelegator->getUsrUserParameterWithRecoveryStamina(
            $usrUserId,
            $now,
        );

        // ショップの条件パックを解放
        $this->shopDelegator->releaseConditionPacks($usrUserId, $usrParameter->getLevel(), $now);

        // 放置インセンティブをリセット
        $this->idleIncentiveDelegator->resetReceiveCount($usrUserId, $now);

        // ステージイベントをリセット
        $this->stageDelegator->resetStageEvent($usrUserId, $now);

        // ログイン回数をインクリメント
        $this->userDelegator->incrementLoginCountAndProcessActions($usrUserId, $platform, $now);

        // ミッション進捗を更新
        $this->missionDelegator->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // 新しいメッセージを追加
        $this->messageDelegator->addNewMessages($usrUserId, $now, $language, $gameStartAt);

        return new GameUpdateData();
    }

    /**
     * ゲームのフェッチ処理（/api/game/fetch）
     */
    public function fetch(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): GameFetchData {
        // ユーザーパラメータを取得
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);

        // 通貨サマリーを取得
        $summary = $this->appCurrencyDelegator->getCurrencySummary($usrUserId);

        $usrParameterData = new UsrParameterData(
            $usrUserParameter->getLevel(),
            $usrUserParameter->getExp(),
            $usrUserParameter->getCoin(),
            $usrUserParameter->getStamina(),
            $usrUserParameter->getStaminaUpdatedAt(),
            $summary->getFreeAmount(),
            $summary->getPaidAmountApple(),
            $summary->getPaidAmountGoogle(),
        );

        // ステージ情報を取得
        $usrStages = $this->usrStageRepository->getListByUsrUserId($usrUserId);

        // バッジ情報を取得
        $gameBadgeData = $this->fetchBadge($usrUserId, $now, $language, $gameStartAt);

        return new GameFetchData(
            $usrParameterData,
            $usrStages,
            // ...
            $gameBadgeData,
            // ...
        );
    }
}
```

**ポイント:**
- 他ドメインのDelegatorを直接DIできる（Gameドメインの特権）
- ゲーム全体のデータを集約して返す
- 複数のDelegatorを組み合わせて処理

## UseCase の実装例

**ファイルパス:** `api/app/Domain/Game/UseCases/GameUpdateAndFetchUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Game\Services\GameService;
use App\Http\Responses\Data\GameUpdateAndFetchData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class GameUpdateAndFetchUseCase
{
    public function __construct(
        private GameService $gameService,
    ) {
    }

    /**
     * ゲームの更新とフェッチを実行（/api/game/update_and_fetch）
     */
    public function __invoke(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): GameUpdateAndFetchData {
        return DB::transaction(function () use (
            $usrUserId,
            $platform,
            $now,
            $language,
            $gameStartAt
        ) {
            // 更新処理
            $updateData = $this->gameService->update(
                $usrUserId,
                $platform,
                $now,
                $language,
                $gameStartAt
            );

            // フェッチ処理
            $fetchData = $this->gameService->fetch(
                $usrUserId,
                $now,
                $language,
                $gameStartAt
            );

            return new GameUpdateAndFetchData($updateData, $fetchData);
        });
    }
}
```

## まとめ

Gameドメインの特徴:

1. **最上位レイヤー**: 他のどのドメインからも依存されない
2. **特権**: 他ドメインのDelegatorを直接DIできる（通常ドメインは禁止）
3. **シンプルな構成**: Services、UseCasesのみ
4. **ゲーム全体の基盤**: ログイン、データフェッチ、バージョン管理等

新規ドメインをGameドメインとして作成するのは稀です。ゲーム全体の基盤機能を追加する場合のみ使用してください。
