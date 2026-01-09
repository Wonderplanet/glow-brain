<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Utils\MasterDataUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;
use WonderPlanet\Entity\MasterEncryptionSetting;

/**
 * マスターデータインポートv2管理ツール用
 * クライアント用のmessagePackファイル(+jsonファイル)を生成するサービス
 */
class SerializeDataGenerateService
{
    private string $configKeyDatabaseCsvDir = 'wp_master_asset_release_admin.databaseCsvDir';
    private string $configKeySerializedFileDir = 'wp_master_asset_release_admin.serializedFileDir';
    private string $configKeySpreadSheetCsvDir = 'wp_master_asset_release_admin.spreadSheetCsvDir';

    public function __construct(
        private readonly ClassSearchService $classSearchService,
        private readonly CsvLoadService $csvLoadService,
        private readonly CsvConvertService $csvConvertService,
        private readonly SerializedDataFileOperator $fileOperator,
        private readonly CSVOperator $csvOperator,
    ) {
    }

    /**
     * @param string $importId
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @return array
     * @throws \Exception
     */
    public function generate(string $importId, MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity): array
    {
        // TODO: マスタデータインポートの進捗がわからないため、各所にLog::infoを入れる
        //       本来はこの内容をダイアログでユーザーに通知できるようにしたい
        \Log::info('Start generating serialized data.');

        // 中間CSVロードと出力先の削除
        \Log::info('Load entities from ' . config($this->configKeyDatabaseCsvDir). '/' . $importId);
        $mstEntities = $this->csvLoadService->loadDatabaseCsv(config($this->configKeyDatabaseCsvDir) . '/' . $importId . '/mst/');
        $oprEntities = $this->csvLoadService->loadDatabaseCsv(config($this->configKeyDatabaseCsvDir) . '/' . $importId . '/opr/');

        // $mstEntities、$oprEntitiesから除外対象のファイルを取り除く
        $excludes = $this->loadExcludeSheetNamesFromCsv();
        /** @var DatabaseCsvEntity $mstEntity */
        $mstEntities = array_filter($mstEntities, function ($mstEntity) use ($excludes) {
            return !in_array($mstEntity->getTitle(), $excludes, true);
        });
        /** @var DatabaseCsvEntity $oprEntity */
        $oprEntities = array_filter($oprEntities, function ($oprEntity) use ($excludes) {
            return !in_array($oprEntity->getTitle(), $excludes, true);
        });

        $masterDataHashMap = [];
        $masterDataI18nHashMap = [];
        $operationDataHashMap = [];
        $operationDataI18nHashMap = [];
        foreach ($mngMasterReleaseKeyEntity->getReleaseKeys() as $releaseKey) {
            $serializedFileDirPath = config($this->configKeySerializedFileDir) . "/{$importId}/{$releaseKey}/";
            \Log::info('Start generating serialized data for release_key: ' . $releaseKey);
            \Log::info('  Output directory: ' . $serializedFileDirPath);

            $this->fileOperator->cleanup($serializedFileDirPath);
            $targetMstEntities = array_filter($mstEntities, function (DatabaseCsvEntity $entity) use ($releaseKey) {
                return ((int) $entity->getReleaseKey()) === $releaseKey;
            });

            \Log::info('Generating master data.');
                $masterDataHashMap[$releaseKey] = $this->outputMessagePackAndJson(
                $targetMstEntities,
                $serializedFileDirPath,
                config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.masterData'),
                MasterData::MASTERDATA
            );

            \Log::info('Generating master data i18n.');
            $masterDataI18nHashMap[$releaseKey] = $this->outputMessagePackAndJsonByI18n(
                $targetMstEntities,
                $serializedFileDirPath,
                config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.masteri18ndata'),
                MasterData::MASTERDATA_I18N,
                MasterData::MASTERDATA_I18N_PATH
            );

            // oprが存在しない場合release_keyとoprClientDataHashの参照が取れないのでそのエラー回避
            if ($oprEntities) {
                $targetOprEntities = array_filter($oprEntities, function (DatabaseCsvEntity $entity) use ($releaseKey) {
                    return ((int) $entity->getReleaseKey()) === $releaseKey;
                });

                \Log::info('Generating operation data.');
                $operationDataHashMap[$releaseKey] = $this->outputMessagePackAndJson(
                    $targetOprEntities,
                    $serializedFileDirPath,
                    config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.operationdata'),
                    MasterData::OPERATIONDATA
                );

                \Log::info('Generating operation data i18n.');
                $operationDataI18nHashMap[$releaseKey] = $this->outputMessagePackAndJsonByI18n(
                    $targetOprEntities,
                    $serializedFileDirPath,
                    config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.operationi18ndata'),
                    MasterData::OPERATIONDATA_I18N,
                    MasterData::OPERATIONDATA_I18N_PATH
                );
            }
        }

        \Log::info('End generating serialized data.');

        return [
            $masterDataHashMap,
            $masterDataI18nHashMap,
            $operationDataHashMap,
            $operationDataI18nHashMap,
        ];
    }

    /**
     * messagePackファイル、jsonファイルを作成して指定位置に配置する
     * @param string $serializedFileDirPath
     * @param string $filePathOfJson
     * @param string $filePathOfMessagePack
     * @param array $data
     * @return void
     */
    private function createMessagePackAndJsonFile(
        string $serializedFileDirPath,
        string $filePathOfJson,
        string $filePathOfMessagePack,
        array $data
    ): void {
        $jsonPath = $serializedFileDirPath.'/'.$filePathOfJson;
        $messagePackPath = $serializedFileDirPath.'/'.$filePathOfMessagePack;
        $this->fileOperator->write($jsonPath, $messagePackPath, $data);
    }

    /**
     * jsonファイルを作成して指定位置に配置する
     * @param string $serializedFileDirPath
     * @param string $filePathOfJson
     * @param array $data
     * @return void
     */
    private function createJsonFile(
        string $serializedFileDirPath,
        string $filePathOfJson,
        array $data
    ): void {
        $jsonPath = $serializedFileDirPath.'/'.$filePathOfJson;
        $this->fileOperator->write($jsonPath, '', $data);
    }

    /**
     * messagePackファイルのハッシュ値を返す
     *
     * @param string $serializedFileDirPath
     * @param string $filePath
     * @return string
     */
    private function getHashFromFile(string $serializedFileDirPath, string $filePath): string
    {
        $file = $serializedFileDirPath . '/' . $filePath;
        return md5_file($file);
    }

    /**
     * シリアライズしたmessagePackとJSONの作成
     *
     * @param array $entities messagePack,JSONに変換候補のEntity
     * @param string $serializedFileDirPath シリアライズしたmessagePack,JSONの吐き出し先
     * @param string $targetNameSpace api側のマスタクラスのネームスペース
     * @param string $target mstかoprかを判別する
     * @return string 生成したファイルのハッシュ値
     */
    private function outputMessagePackAndJson(
        array $entities,
        string $serializedFileDirPath,
        string $targetNameSpace,
        string $target
    ): string {
        $releaseKey = '';
        $masterDataClasses = $this->classSearchService->getClassNamesInNameSpace($targetNameSpace);
        if (empty($masterDataClasses)) {
            return '';
        }

        // リソースクラスパス(例:App\Http\Resources\Api\Masterdata\\)を作成
        $resourceClassPath = $targetNameSpace . '\\';

        $outputData = [];
        /** @var DatabaseCsvEntity $entity */
        foreach ($entities as $entity) {
            \Log::info('  Generating ' . $entity->getTitle());

            // schemaに沿った出力のため、XXXResourceクラスを使う
            $targets = array_filter($masterDataClasses, function ($name) use ($entity, $resourceClassPath) {
                return strtolower($name) === strtolower($resourceClassPath . $entity->getTitle() . 'Resource');
            });
            $targetClassName = !empty($targets) ? reset($targets) : null;

            if (is_null($targetClassName)) continue;
            $resource = new $targetClassName;

            $data = $this->csvConvertService->convertToDataArrayUsePlaceholder($entity->getData());
            $models = [];
            $tableName = "";
            foreach ($data as $row) {
                $model = $this->classSearchService->createMasterModelClass($entity->getTitle());
                if (empty($model)) continue;
                if (empty($tableName)) $tableName = $model->getTable();
                // 改行コードを改行に変換
                $row = MasterDataImportUtility::convertToSystemEOLFromDatabaseCsv($row);
                $model->fill($row);
                $models[] = $model;
            }

            // シリアライズ
            $resource->build($models);
            $outputData[lcfirst($entity->getTitle())] = $resource->toArray();

            if ($releaseKey === '') {
                $releaseKey = $entity->getReleaseKey();
            }
        }

        if ($releaseKey === '') {
            throw new \Exception('release_keyがありません');
        }

        \Log::info('  create file: Release key: ' . $releaseKey);

        // json, messagePackのファイルを作成して配置する
        $jsonFileName = "{$target}.json.tmp";
        $jsonFilePath = "{$target}/" . $jsonFileName;
        $fileName = "{$target}.data";
        $msgPackFilePath = "{$target}/" . $fileName;
        $this->createJsonFile($serializedFileDirPath, $jsonFilePath, $outputData);

        // client_hashをmessagePackのファイルパスから作成
        $clientDataHash = $this->getHashFromFile($serializedFileDirPath, $jsonFilePath);
        // S3用ファイルパスを作成してリネームする
        $newFilePathOfJson = MasterDataUtility::getPath($target, $clientDataHash);
        rename("{$serializedFileDirPath}/{$jsonFilePath}", "{$serializedFileDirPath}/{$newFilePathOfJson}");

        // 暗号化用とレスポンス用にmessagePackファイルのハッシュ値を取得
        $newClientDataHash = $this->getHashFromFile($serializedFileDirPath, $newFilePathOfJson);

        // 対象ファイルを暗号化する
        $this->encryptFile($serializedFileDirPath, $newClientDataHash, $newFilePathOfJson);

        return $newClientDataHash;
    }

    /**
     * シリアライズしたmessagePack,JSONの作成(i18n用)
     *
     * @param array $entities messagePack,JSONに変換候補のEntity
     * @param string $serializedFileDirPath シリアライズしたmessagePack,JSONの吐き出し先
     * @param string $targetNameSpace api側のマスタクラスのネームスペース
     * @param string $target mst_i18nかopr_i18nかを判別する
     * @param string $targetPath i18n用のファイルパス
     * @return array<string, string>
     * @throws \Exception
     */
    private function outputMessagePackAndJsonByI18n(
        array $entities,
        string $serializedFileDirPath,
        string $targetNameSpace,
        string $target,
        string $targetPath,
    ): array {
        $releaseKey = '';

        $masterDataClasses = $this->classSearchService->getClassNamesInNameSpace($targetNameSpace);
        if (empty($masterDataClasses)) {
            return [];
        }

        // リソースクラスパス(例:App\Http\Resources\Api\Masteri18ndata\\)を作成
        $resourceClassPath = $targetNameSpace . '\\';

        $outputData = [];
        /** @var DatabaseCsvEntity $entity */
        foreach ($entities as $entity) {
            \Log::info('  Generating ' . $entity->getTitle());

            // schemaに沿った出力のため、XXXResourceクラスを使う
            $targets = array_filter($masterDataClasses, function ($name) use ($entity, $resourceClassPath) {
                return strtolower($name) === strtolower($resourceClassPath . $entity->getTitle() . 'Resource');
            });
            $targetClassName = !empty($targets) ? reset($targets) : null;

            if (is_null($targetClassName)) continue;
            $resource = new $targetClassName;

            $data = $this->csvConvertService->convertToDataArrayUsePlaceholder($entity->getData());
            $models = [];
            $languages = [];
            $tableName = "";
            foreach ($data as $row) {
                $model = $this->classSearchService->createMasterModelClass($entity->getTitle());
                if (empty($model)) continue;
                if (empty($tableName)) $tableName = $model->getTable();
                $languageEnum = Language::from($row['language']);
                if (!in_array($languageEnum, $languages, true)) {
                    $models[$languageEnum->value] = [];
                    $languages[] = $languageEnum;
                }
                // 改行コードを改行に変換
                $row = MasterDataImportUtility::convertToSystemEOLFromDatabaseCsv($row);
                $model->fill($row);
                $models[$languageEnum->value][] = $model;
            }

            if (empty($data)) {
                // データがない場合は空の配列をセット
                $outputData[Language::Ja->value][lcfirst($entity->getTitle())] = [];
            } else {
                // シリアライズ
                foreach ($languages as $language) {
                    $resource->build($models[$language->value]);
                    $outputData[$language->value][lcfirst($entity->getTitle())] = $resource->toArray();
                }
            }

            if ($releaseKey === '') {
                $releaseKey = $entity->getReleaseKey();
            }
        }

        if ($releaseKey === '') {
            throw new \Exception('release_keyがありません');
        }
        \Log::info('  create file: Release key: ' . $releaseKey);

        $clientDataHashList = [];

        if (empty($languages)) {
            $languages[] = Language::Ja;
        }
        foreach ($languages as $language) {
            // json, messagePackのファイルを作成して配置する
            $jsonFileName = "{$target}_{$language->value}.json.tmp";
            $jsonFilePath = "{$targetPath}/" . $jsonFileName;
            $this->createJsonFile($serializedFileDirPath, $jsonFilePath, $outputData[$language->value]);
            // client_hashをmessagePackのファイルパスから作成
            $clientDataHash = $this->getHashFromFile($serializedFileDirPath, $jsonFilePath);
            // S3用ファイルパスを作成してリネームする
            $newFilePathOfJson = MasterDataUtility::getI18nPath(
                $targetPath,
                $target,
                $language,
                $clientDataHash
            );
            rename("{$serializedFileDirPath}/{$jsonFilePath}", "{$serializedFileDirPath}/{$newFilePathOfJson}");
            // 暗号化用とレスポンス用にmessagePackファイルのハッシュ値を取得
            $newClientDataHash = $this->getHashFromFile($serializedFileDirPath, $newFilePathOfJson);

            // 対象ファイルを暗号化する
            $this->encryptFile($serializedFileDirPath, $newClientDataHash, $newFilePathOfJson);
            $clientDataHashList[$language->value] = $newClientDataHash;
        }
        return $clientDataHashList;
    }

    /**
     * CSVからクライアントへのシリアライズを除外するシート名を取得する
     * @return array
     */
    private function loadExcludeSheetNamesFromCsv() : array
    {
        $settingCsvPath = config($this->configKeySpreadSheetCsvDir) . '/' . config('wp_master_asset_release_admin.serializeDataOutputSettingSheetName') . '.csv';
        $filterSheetData = $this->csvOperator->read($settingCsvPath);

        $result = [];
        $sheetNameIndex = -1;
        $sheetAvailableIndex = -1;
        foreach ($filterSheetData as $row) {
            if (in_array('masterName', $row)) {
                $sheetNameIndex = array_search('masterName', $row);
                $sheetAvailableIndex = array_search('available', $row);
                continue;
            }
            if ($sheetNameIndex != -1) {
                if ($row[$sheetAvailableIndex] == 'FALSE' || $row[$sheetAvailableIndex] == null || $row[$sheetAvailableIndex] == '0') {
                    $result[] = $row[$sheetNameIndex];
                }
            }
        }
        return $result;
    }

    /**
     * 暗号化ありでファイルを生成する
     * 
     * messagePackFilePathにあるファイルを暗号化して置き換える
     * 
     * @param string $serializedFileDirPath 出力先のルートディレクトリ
     * @param string $hash ハッシュ
     * @param string $messagePackFilePath 出力先のmessagePackファイル名
     * @return void
     */
    private function encryptFile(
        string $serializedFileDirPath,
        string $hash,
        string $messagePackFilePath,
    ): void {
        $masterEncryptionSetting = MasterEncryptionSetting::createUsingHash($hash);

        // 暗号化が有効でない場合は何もしない
        if (!$masterEncryptionSetting->enableEncrypted()) {
            return;
        }

        $this->fileOperator->gzipAndEncryptMasterdataFile(
            $serializedFileDirPath . '/' . $messagePackFilePath,
            $serializedFileDirPath . '/' . $messagePackFilePath,
            $masterEncryptionSetting,
        );
    }
}
