<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * Domainのサービスプロバイダの基底クラス
 *
 * こちらのクラスを使用する場合、強制的に遅延プロバイダとして登録される。
 * (遅延プロバイダかどうかはDeferrableProviderの有無で判断される)
 *
 * 遅延プロバイダとして動作させたくない場合は、BaseServiceProviderを使用すること
 */
abstract class BaseDeferrableServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * 遅延プロバイダとして登録するクラスのリストを返す
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return array_merge(
            $this->classes,
            array_keys($this->facades),
        );
    }
}
