<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Currency\Services\CurrencyUserService;
use App\Domain\Resource\Entities\CurrencyTriggers\DebugTrigger;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class GrantUserCurrencyMaxUseCase extends BaseCommands
{
    protected string $name = 'ユーザーの所持コイン、無償プリズムMAX';
    protected string $description = 'ユーザーの所持コイン、無償プリズムをMAXにします';

    public function __construct(
        private CurrencyUserService $currencyUserService,
        private UsrUserParameterRepository $usrUserParameterRepository,
        private CurrencyService $currencyService,
    ) {
    }

    /**
     * デバッグ機能:ユーザーの所持コイン、無償プリズムMAX
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        // ユーザーの所持無償プリズムを取得
        $freeDiamondCount = $this->currencyUserService->getFreeDiamond($user->id);
        $grantFreeDiamondCount = $this->currencyService->getMaxOwnedCurrencyFreeAmount() - $freeDiamondCount;

        // ユーザーの所持無償プリズムをMAXにする
        $this->currencyUserService->addIngameFreeDiamond(
            $user->id,
            $platform,
            $grantFreeDiamondCount,
            new DebugTrigger($this->name),
        );

        // コインの所持数を取得
        $userParameter = UsrUserParameter::where('usr_user_id', $user->id)->first();
        // ユーザーの所持コインをMAXにする
        $userParameter->addCoin(UserConstant::MAX_COIN_COUNT - $userParameter->getCoin());
        $this->usrUserParameterRepository->syncModel($userParameter);
    }
}
