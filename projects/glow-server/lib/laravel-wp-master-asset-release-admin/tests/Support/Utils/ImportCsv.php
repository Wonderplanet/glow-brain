<?php

namespace WonderPlanet\Tests\Support\Utils;

/**
 * CSVインポートクラス
 */
class ImportCsv extends BaseImportCsv
{
    /**
     * fixtureのあるルートディレクトリを取得する
     *
     * デフォルトディレクトリは、getFixtureRootDir() . '/default'となる
     * Fixtureディレクトリは、getFixtureRootDir() . '/クラス名/fixture名'となる
     *
     * @return string
     */
    public function getFixtureRootDir(): string
    {
        return __DIR__ . '/../../fixtures';
    }

    /**
     * Modelクラスを配置しているディレクトリパスを取得する
     * adminコンテナで見た該当ライブラリのパスとなる
     *
     * @return string
     */
    public function getModelClassPass(): string
    {
        return __DIR__ . '/../../../src/Domain/MasterAssetReleaseAdmin/Models/';
    }
}
