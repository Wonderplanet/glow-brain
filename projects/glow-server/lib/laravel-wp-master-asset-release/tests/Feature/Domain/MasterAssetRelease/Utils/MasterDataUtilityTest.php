<?php

namespace Feature\Domain\MasterAssetRelease\Utils;

use Tests\TestCase;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Utils\MasterDataUtility;

class MasterDataUtilityTest extends TestCase
{
    /**
     * @test
     * @dataProvider getPathData
     */
    public function getPath_パスチェック(string $target): void
    {
        // Setup
        $hash = 'hash_test';

        // Exercise
        $actual = MasterDataUtility::getPath($target, $hash);

        // Verify
        $this->assertEquals("{$target}/{$target}_{$hash}.json", $actual);
    }

    /**
     * @test
     * @dataProvider getPathData
     */
    public function getPathOfMessagePack_パスチェック(string $target): void
    {
        // Setup
        $hash = 'hash_test';

        // Exercise
        $actual = MasterDataUtility::getPathOfMessagePack($target, $hash);

        // Verify
        $this->assertEquals("{$target}/{$target}_{$hash}.data", $actual);
    }

    /**
     * @return array[]
     */
    private function getPathData(): array
    {
        return [
            'masterData' => [MasterData::MASTERDATA],
            'operationData' => [MasterData::OPERATIONDATA],
        ];
    }

    /**
     * @test
     * @dataProvider getI18nPathData
     */
    public function getI18nPath_パスチェック(string $i18nPath, string $target, Language $language): void
    {
        // Setup
        $hash = 'hash_test';

        // Exercise
        $actual = MasterDataUtility::getI18nPath($i18nPath, $target, $language, $hash);

        // Verify
        $this->assertEquals("{$i18nPath}/{$target}_{$language->value}_{$hash}.json", $actual);
    }

    /**
     * @test
     * @dataProvider getI18nPathData
     */
    public function getI18nPathOfMessagePack_パスチェック(string $i18nPath, string $target, Language $language): void
    {
        // Setup
        $hash = 'hash_test';

        // Exercise
        $actual = MasterDataUtility::getI18nPathOfMessagePack($i18nPath, $target, $language, $hash);

        // Verify
        $this->assertEquals("{$i18nPath}/{$target}_{$language->value}_{$hash}.data", $actual);
    }

    /**
     * @return array[]
     */
    private function getI18nPathData(): array
    {
        return [
            'masterData.Ja' => [MasterData::MASTERDATA_I18N_PATH, MasterData::MASTERDATA_I18N, Language::Ja],
            'masterData.En' => [MasterData::MASTERDATA_I18N_PATH, MasterData::MASTERDATA_I18N, Language::En],
            'masterData.Zh' => [MasterData::MASTERDATA_I18N_PATH, MasterData::MASTERDATA_I18N, Language::Zh_Hant],
            'operationData.Ja' => [MasterData::OPERATIONDATA_I18N_PATH, MasterData::OPERATIONDATA_I18N, Language::Ja],
            'operationData.En' => [MasterData::OPERATIONDATA_I18N_PATH, MasterData::OPERATIONDATA_I18N, Language::En],
            'operationData.Zh' => [MasterData::OPERATIONDATA_I18N_PATH, MasterData::OPERATIONDATA_I18N, Language::Zh_Hant],
        ];
    }
}
