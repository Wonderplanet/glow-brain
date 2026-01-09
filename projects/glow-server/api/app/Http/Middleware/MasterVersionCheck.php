<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mng\Services\MngMasterReleaseService;
use Closure;
use Illuminate\Http\Request;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;

readonly class MasterVersionCheck
{
    public function __construct(
        private readonly MngMasterReleaseService $mngMasterReleaseService,
    ) {
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @throws GameException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        $parsed = $this->parseAndValidateHeader($request);
        if (is_null($parsed)) {
            // ヘッダーが存在しない場合は、そのまま処理を継続（主に初回アクセス時を想定）
            return $next($request);
        }

        [
            $mstHash,
            $oprHash,
            $mstI18nHash,
            $oprI18nHash,
            $clientVersion
        ] = $parsed;

        if ($this->skipCheck($request->path())) {
            // チェック不要なAPIパスの場合は、そのまま処理を継続
            return $next($request);
        }

        // 現在配信中のリリース情報を取得
        $now = app(Clock::class)->now();
        $versionEntity = $this->mngMasterReleaseService->getMasterReleaseVersionByClientVersion($clientVersion, $now);

        $language = $request->header(System::HEADER_LANGUAGE);
        $languageEnum = Language::from($language);
        if (
            $this->checkMstRequiredUpdate($mstHash, $versionEntity)
            || $this->checkOprRequiredUpdate($oprHash, $versionEntity)
            || $this->checkMstI18nRequiredUpdate($mstI18nHash, $languageEnum, $versionEntity)
            || $this->checkOprI18nRequiredUpdate($oprI18nHash, $languageEnum, $versionEntity)
        ) {
            // マスターデータの更新が必要な場合は、クライアント側にその旨を通知する
            throw new GameException(ErrorCode::REQUIRE_RESOURCE_UPDATE);
        }

        return $next($request);
    }

    /**
     * HTTPヘッダーからreleaseKeyとhashを取り出す
     *
     * @param Request $request
     * @return array{string, string, ?string, ?string, ?string}|null
     */
    private function parseAndValidateHeader(Request $request): ?array
    {
        $mstHash = $request->header(System::HEADER_MASTER_HASH);
        $oprHash = $request->header(System::HEADER_OPERATION_HASH);
        $mstI18nHash = $request->header(System::HEADER_MASTER_I18N_HASH);
        $oprI18nHash = $request->header(System::HEADER_OPERATION_I18N_HASH);

        $clientVersion = $request->header(System::CLIENT_VERSION);

        // データ構造上i18nハッシュは空になる可能性があるのでここではチェックを行わない
        if (!isset($mstHash, $oprHash)) {
            return null;
        }

        return [
            $mstHash,
            $oprHash,
            $mstI18nHash,
            $oprI18nHash,
            $clientVersion
        ];
    }

    /**
     * マスターバージョン更新チェックをスキップするかの判定
     *
     * @param string $path
     * @return bool
     * @throws GameException
     */
    private function skipCheck(string $path): bool
    {
        return isset(System::MASTER_CHECK_THROUGH_API[$path]);
    }

    /**
     * クライアントマスターデータバージョン更新チェック
     *
     * @param string $hash
     * @param MngMasterReleaseVersionEntity $masterReleaseVersionEntity
     * @return bool
     */
    private function checkMstRequiredUpdate(
        string $hash,
        MngMasterReleaseVersionEntity $masterReleaseVersionEntity
    ): bool {
        return $masterReleaseVersionEntity->getClientMstDataHash() !== $hash;
    }

    /**
     * 指定した言語のクライアントマスターデータバージョンチェック
     *
     * @param string|null $hash
     * @param Language $language
     * @param MngMasterReleaseVersionEntity $masterReleaseVersionEntity
     * @return bool
     */
    private function checkMstI18nRequiredUpdate(
        ?string $hash,
        Language $language,
        MngMasterReleaseVersionEntity $masterReleaseVersionEntity
    ): bool {
        if (is_null($hash)) {
            return false;
        }

        return $masterReleaseVersionEntity->getClientMstDataI18nHashByLanguage($language) !== $hash;
    }

    /**
     * クライアントオペレーションデータバージョン更新チェック
     *
     * @param string $hash
     * @param MngMasterReleaseVersionEntity $masterReleaseVersionEntity
     * @return bool
     */
    private function checkOprRequiredUpdate(
        string $hash,
        MngMasterReleaseVersionEntity $masterReleaseVersionEntity
    ): bool {
        return $masterReleaseVersionEntity->getClientOprDataHash() !== $hash;
    }

    /**
     * 指定した言語のクライアントオペレーションデータバージョンチェック
     *
     * @param string|null $hash
     * @param Language $language
     * @param MngMasterReleaseVersionEntity $masterReleaseVersionEntity
     * @return bool
     */
    private function checkOprI18nRequiredUpdate(
        ?string $hash,
        Language $language,
        MngMasterReleaseVersionEntity $masterReleaseVersionEntity
    ): bool {
        if (is_null($hash)) {
            return false;
        }

        return $masterReleaseVersionEntity->getClientOprDataI18nHashByLanguage($language) !== $hash;
    }
}
