<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;

/**
 * 配信中または配信準備中のMngMasterRelease.ReleaseKeyの情報を持つクラス
 * csvやjsonファイル書き出し時に参照するために使用する
 */
class MngMasterReleaseKeyEntity
{
    private Collection $mngMasterReleases;

    public function __construct(
        private readonly int $latestApplyReleaseKeyBy,
        private readonly Collection $mngMasterReleasesByApplyOrPending,

    ) {
        // 必要なデータを読み込む
        $this->initialize();
    }

    /**
     * 初期化
     *
     * @return void
     */
    private function initialize(): void
    {
        // 配信中/配信準備中のデータを加工して保持する
        $this->mngMasterReleases = $this->mngMasterReleasesByApplyOrPending
            ->map(function (MngMasterRelease $release) {
                $status = 'pending';
                if ($release->release_key === $this->latestApplyReleaseKeyBy) {
                    // リリース済みのrelease_keyと一致したら配信中とする
                    $status = 'apply';
                }

                // release_keyに紐づいたgit_revisionを取得
                $mngMasterReleaseVersions = $release->mngMasterReleaseVersion();
                $mngMasterReleaseVersion = $mngMasterReleaseVersions->first();
                $serverDbHash = $mngMasterReleaseVersion?->server_db_hash;

                return [
                    'releaseKey' => $release->release_key,
                    'status' => $status,
                    'serverDbHash' => $serverDbHash,
                ];
            });
    }

    /**
     * リリースキーのリストを取得する
     *
     * @return array
     */
    public function getReleaseKeys(): array
    {
        return $this->mngMasterReleases
            ->map(fn ($row) => $row['releaseKey'])
            ->toArray();
    }

    /**
     * マスターDB名を生成するのに必要なパラメータを返す
     * 取得優先度は下記
     *  1.配信中のリリースキー + serverDbHash
     *  2.配信準備中のリリースキー + serverDbHash
     *  3.新規追加されたリリースキー + 空文字
     *
     * @return array
     */
    public function getMasterDbNameParameter(): array
    {
        // 最新の配信中の情報を取得する
        /** @var array|null $result */
        $result = $this->mngMasterReleases
            ->first(fn($row) => $row['status'] === 'apply');

        if (is_null($result)) {
            // 配信中の情報がなければ、一番最新のリリースキーを取得する
            $result = $this->mngMasterReleases
                ->sortByDesc(fn($row) => $row['releaseKey'])
                ->first();
        }

        // リリースキーと新しく生成したserverDbHashからマスターDB名となる文字列を生成して返す
        // 対象リリースキーのserverDbHashがない場合はDBがまだ存在しない可能性もあるのでエラーにはしない(使用先で制御する)
        $releaseKey = $result['releaseKey'];
        $serverDbHash = $result['serverDbHash'] ?? '';

        return [
            'releaseKey' => $releaseKey,
            'serverDbHashMap' => [$releaseKey => $serverDbHash],
        ];
    }
}
