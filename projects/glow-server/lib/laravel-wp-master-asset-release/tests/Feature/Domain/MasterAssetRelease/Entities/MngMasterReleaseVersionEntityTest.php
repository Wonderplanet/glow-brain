<?php

namespace Feature\Domain\MasterAssetRelease\Entities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MngMasterReleaseVersionEntityTest extends TestCase
{
    use RefreshDatabase;
    
    // fixtures/defaultのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     * @dataProvider getClientMstDataI18nHashByLanguageData
     */
    public function getClientMstDataI18nHashByLanguage_言語ごとにmstデータハッシュが取得できるか(
        Language $language,
        string $expected
    ): void {
        // Setup
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'client_mst_data_i18n_ja_hash' => 'mst_ja_hash',
                'client_mst_data_i18n_en_hash' => 'mst_en_hash',
                'client_mst_data_i18n_zh_hash' => 'mst_zh_hash',
            ]);
        $entity = $mngMasterReleaseVersion->toEntity();

        // Exercise
        $actual = $entity->getClientMstDataI18nHashByLanguage($language);
        
        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getClientMstDataI18nHashByLanguageData(): array
    {
        return [
            'ja' => [Language::Ja, 'mst_ja_hash'],
            'en' => [Language::En, 'mst_en_hash'],
            'zh' => [Language::Zh_Hant, 'mst_zh_hash'],
        ];
    }
    
    /**
     * @test
     * @dataProvider getClientOprDataI18nHashByLanguageData
     */
    public function getClientOprDataI18nHashByLanguage_言語ごとにoprデータハッシュが取得できるか(
        Language $language,
        string $expected
    ): void {
        // Setup
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'client_opr_data_i18n_ja_hash' => 'opr_ja_hash',
                'client_opr_data_i18n_en_hash' => 'opr_en_hash',
                'client_opr_data_i18n_zh_hash' => 'opr_zh_hash',
            ]);
        $entity = $mngMasterReleaseVersion->toEntity();
        
        // Exercise
        $actual = $entity->getClientOprDataI18nHashByLanguage($language);
        
        // Verify
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @return array[]
     */
    private function getClientOprDataI18nHashByLanguageData(): array
    {
        return [
            'ja' => [Language::Ja, 'opr_ja_hash'],
            'en' => [Language::En, 'opr_en_hash'],
            'zh' => [Language::Zh_Hant, 'opr_zh_hash'],
        ];
    }
    
    /**
     * @test
     */
    public function getDbName_データベース名取得チェック(): void
    {
        // Setup
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'release_key' => '2024111401',
                'server_db_hash' => 'hash-abcd-1234',
            ]);
        $entity = $mngMasterReleaseVersion->toEntity();
        
        // Exercise
        $actual = $entity->getDbName();

        // Verify
        $expected = config('app.env') . '_mst_2024111401_hash-abcd-1234';
        $this->assertEquals($expected, $actual);
    }
}
