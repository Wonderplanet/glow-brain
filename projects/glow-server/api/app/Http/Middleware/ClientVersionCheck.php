<?php

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mng\Repositories\MngClientVersionRepository;
use App\Domain\User\Constants\UserConstant;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientVersionCheck
{
    /**
     * @throws GameException
     * @throws BindingResolutionException
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $validated = $this->parseAndValidateHeader($request);

        if (is_null($validated)) {
            throw new GameException(ErrorCode::VALIDATION_ERROR);
        }
        [$clientVersion, $platform] = $validated;

        if ($this->checkRequireUpdate($clientVersion, $platform)) {
            throw new GameException(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return array<mixed>|null
     * @throws GameException
     */
    private function parseAndValidateHeader(Request $request): ?array
    {
        $clientVersion = $request->header('CLIENT_VERSION');
        $platform = $request->header('PLATFORM');
        if (!isset($clientVersion, $platform)) {
            return null;
        }

        $validator = Validator::make(compact('clientVersion', 'platform'), [
            'clientVersion' => 'required|regex:/^(\d+\.)+(\d+\.)+(\d+)$/',
            'platform' => 'required|in:' . implode(',', array_keys(UserConstant::PLATFORM_STRING_LIST)),
        ]);
        if ($validator->fails()) {
            return null;
        }

        $validated = $validator->safe()->only(['clientVersion', 'platform']);;

        return [
            $validated['clientVersion'],
            $validated['platform']
        ];
    }

    /**
     * クライアントバージョン更新チェック
     * @throws BindingResolutionException
     */
    private function checkRequireUpdate(string $clientVersion, int $platform): bool
    {
        /** @var MngClientVersionRepository $mngClientVersionRepository */
        $mngClientVersionRepository = app()->make(MngClientVersionRepository::class);
        $mngClientVersion = $mngClientVersionRepository->findByVersion($clientVersion, $platform);
        if (is_null($mngClientVersion)) {
            return true;
        }

        return $mngClientVersion->isRequireUpdate();
    }
}
