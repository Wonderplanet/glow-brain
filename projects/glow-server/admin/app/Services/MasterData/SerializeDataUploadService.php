<?php

namespace App\Services\MasterData;

use App\Domain\Resource\Mst\Models\MstReleaseKey;
use App\Models\Opr\OprMasterReleaseControl;
use App\Operators\CSVOperator;
use App\Operators\S3Operator;
use App\Operators\SerializedDataFileOperator;
use App\Services\MasterData\ClassSearchService;
use App\Services\MasterData\CsvConvertService;
use App\Services\MasterData\CsvLoadService;
use Illuminate\Database\Eloquent\Model;
use WonderPlanet\Entity\MasterEncryptionSetting;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class SerializeDataUploadService
{
    private ClassSearchService $classSearchService;
    private CsvLoadService $csvLoadService;
    private CsvConvertService $csvConvertService;

    private SerializedDataFileOperator $fileOperator;
    private S3Operator $s3Operator;
    private CSVOperator $csvOperator;

    // private AesRequestEncryptor $cryptography;

    public function __construct()
    {
        $this->classSearchService = new ClassSearchService();
        $this->csvLoadService = new CsvLoadService();
        $this->csvConvertService = new CsvConvertService();

        $this->fileOperator = new SerializedDataFileOperator();
        $this->s3Operator = new S3Operator();
        $this->csvOperator = new CSVOperator();

        // $this->cryptography = new AesRequestEncryptor();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function upload(): void
    {
        // 中間CSVロードと出力先の削除
        $mstEntities = $this->csvLoadService->loadDatabaseCsv(config('admin.databaseCsvDir'). '/mst/');
        $oprEntities = $this->csvLoadService->loadDatabaseCsv(config('admin.databaseCsvDir'). '/opr/');
        $serializedFileDirPath = config('admin.serializedFileDir');
        $this->fileOperator->cleanup($serializedFileDirPath);

        // CSVごとにファイル生成
        $excludes = $this->loadExcludeSheetNamesFromCsv();
        $releaseKey = '';
        $i18nReleaseKey = '';
        $oprReleaseKey = '';
        $oprI18nReleaseKey = '';
        $hash = '';
        $oprHash = '';
        $mstI18nHash = '';
        $oprI18nHash = '' ;

        list($releaseKey, $hash) = $this->outputJson($mstEntities, $excludes, $serializedFileDirPath, "mst");
        list($i18nReleaseKey, $mstI18nHash) = $this->outputJson($mstEntities, $excludes, $serializedFileDirPath, "mstI18n");
        // oprが存在しない場合release_keyとoprClientDataHashの参照が取れないのでそのエラー回避
        if ($oprEntities) {
            list($oprReleaseKey, $oprHash) = $this->outputJson($oprEntities, $excludes, $serializedFileDirPath, "opr");
            list($oprI18nReleaseKey, $oprI18nHash) = $this->outputJson($oprEntities, $excludes, $serializedFileDirPath, "oprI18n");
            if ($oprReleaseKey !== $releaseKey || $oprHash !== $hash) {
                throw new \Exception('OprとMstでrelease_keyまたはgit_revisionが一致しません');
            }
        }

        $mstReleaseKey = MstReleaseKey::select('*')
            ->where('release_key', '=', $releaseKey)
            ->first();

        // opr_mst_release_controlsに挿入する
        // TODO 一時的に同一のコミットがある場合はそちらのレコードを参照する(release_keyとgit_revisionのユニークにした方が良さそう)
        $releaseControl = OprMasterReleaseControl::select('*')
            ->where('git_revision', '=', $hash)
            ->first();
        // TODO データ投入のタイミング的にdbにrelease_keyが登録されていないとデフォルトが代入されてしまう
        $releaseControl->release_at = $mstReleaseKey->start_at ?? '2023-11-20 00:00:00';
        $releaseControl->release_description = '';
        $releaseControl->save();

        //TODO マニフェストファイルは使用方針が未定のため、コメントアウト
//        // リリースキーのマニフェストファイル作成
//        foreach ($versions as [$releaseKey, $hash]) {
//            // 暗号化ありのマニフェスト生成
//            $manifestForEncrypted = $this->createManifest($serializedFileDirPath, $releaseKey.'/'.$hash);
//            $this->writeEncrypted($serializedFileDirPath, $releaseKey, $hash, 'manifest.json', $manifestForEncrypted);
//
//            // 暗号化なしのマニフェスト生成
//            $masterEncryptionSetting = MasterEncryptionSetting::create((int)$releaseKey, $hash);
//            if (!$masterEncryptionSetting->createEncryptedOnly()) {
//                // 暗号化なしのjsonファイルを参照してマニフェストを生成する
//                $manifestForDecrypted = $this->createManifest($serializedFileDirPath, 'decrypted/'.$releaseKey.'/'.$hash);
//                $this->writeDecrypted($serializedFileDirPath, $releaseKey, $hash, 'manifest.json', $manifestForDecrypted);
//            }
//        }

        // serializedFileDir以下を丸々S3へアップロード
        // TODO: クライアント側で参照先が共通化されたら、環境ごとの方だけアップロードする
        // 各マスターのjsonはこちらが参照される
        $this->s3Operator->uploadDirectory($serializedFileDirPath);

        //TODO マニフェストファイルのアップロード処理も一旦コメントアウト
//        // manifestはこちらが参照される
//        $destinationPathPrefix = config('app.env') . '/';
//        $this->s3Operator->uploadDirectory($serializedFileDirPath, $destinationPathPrefix);
    }

    /**
     * @param string $baseDirPath
     * @param string $filePath
     * @return string
     */
    private function createHashFromFile(string $baseDirPath, string $filePath): string
    {
        $file = $baseDirPath . '/' . $filePath;
        return md5_file($file);
    }

    /**
     * 作成したシリアライズファイルのマニフェストを作成する
     * @param string $baseDirPath 検索対象のディレクトリパス
     * @param string $dir 検索ディレクトリの中で対象にするディレクトリ
     * @return array マニフェストJSON形式のarray
     */
    public function createManifest(string $baseDirPath, string $dir): array
    {
        $tmp = explode('/', $dir);
        $manifest = [
            'release_key' => $tmp[0],
            'hash' => $tmp[1],
            'master_file_list' => [],
        ];
        $files = glob($baseDirPath . '/' . $dir . '/*.json');
        $size = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileSize = filesize($file);
                $size += $fileSize;
                $manifest['master_file_list'][] = [
                    'name' => basename($file),
                    'md5' => md5_file($file),
                    'size' => $fileSize,
                ];
            }
        }
        $manifest['size'] = $size;
        return $manifest;
    }

    /**
     * シリアライズしたJSONの作成とリリースコントロールの更新
     * @param array $entities JSONに変換候補のEntitiy
     * @param array $excludes JSONにしないEntity
     * @param string $serializedFileDirPath シリアライズしたJSONの吐き出し先
     * @param string $target 更新するDBを指定
     * @return array マニフェストJSON形式のarray
     */
    public function outputJson(array $entities, array $excludes, string $serializedFileDirPath, string $target): array
    {
        $releaseKey = '';
        $hash = '';
        if ($target === "mst") {
            $targetNameSpace = 'App\Http\Resources\Api\Masterdata';
            $resourceClassPath = 'App\Http\Resources\Api\Masterdata\\';
        } elseif ($target === "opr") {
            $targetNameSpace = 'App\Http\Resources\Api\Operationdata';
            $resourceClassPath = 'App\Http\Resources\Api\Operationdata\\';
        } elseif ($target === "mstI18n") {
            $targetNameSpace = 'App\Http\Resources\Api\Masteri18ndata';
            $resourceClassPath = 'App\Http\Resources\Api\Masteri18ndata\\';
        } elseif ($target === "oprI18n") {
            $targetNameSpace = 'App\Http\Resources\Api\Operationi18ndata';
            $resourceClassPath = 'App\Http\Resources\Api\Operationi18ndata\\';
        } else {
            throw new \Exception('対象が指定されていません');
        }
        $masterDataClasses = $this->classSearchService->getClassNamesInNameSpace($targetNameSpace);
        if (empty($masterDataClasses)) {
            return [$releaseKey, $hash];
        }
        foreach ($entities as $entity) {
            // ReleaseKeyマスターなどのクライアントに渡さないシートはスキップ
            if (in_array($entity->getTitle(), $excludes)) continue;

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
                if ($target === "mstI18n" || $target === "oprI18n") {
                    if (!in_array($row['language'], $languages)) {
                        $models[$row['language']] = [];
                        $languages[] = $row['language'];
                    }
                    $model->fill($row);
                    $models[$row['language']][] = $model;
                } else {
                    $model->fill($row);
                    $models[] = $model;
                }

            }

            // jsonシリアライズ
            if ($target === "mstI18n" || $target === "oprI18n") {
                foreach ($languages as $language) {
                    $resource->build($models[$language]);
                    // jsonシリアライズ
                    $json[$language][lcfirst($entity->getTitle())] = $resource->toArray();
                }
            } else {
                $resource->build($models);
                $json[lcfirst($entity->getTitle())] = $resource->toArray();
            }

            if ($releaseKey === '') {
                $releaseKey = $entity->getReleaseKey();
            }
            if ($hash === '') {
                $hash = $entity->getGitRevision();
            }
            // 現状はrelease_keyとhashはすべて同じになる想定なのでチェック
            if ($releaseKey !== $entity->getReleaseKey() || $hash !== $entity->getGitRevision()) {
                throw new \Exception('インポートしたrelease_keyまたはgit_revisionが異なっています');
            }
        }

        if ($releaseKey === '' || $hash === '') {
            throw new \Exception('release_keyまたはgit_revisionがありません');
        }
        // リリースコントロール更新
        // TODO 更新をこのメソッドの外側に出したい
        $releaseControl = OprMasterReleaseControl::select('*')
            ->where('git_revision', '=', $hash)
            ->first();
        if(is_null($releaseControl))
        {
            $releaseControl = new OprMasterReleaseControl();
            $releaseControl->id = (string) Uuid::uuid4();
            $releaseControl->release_key = $releaseKey;
            $releaseControl->git_revision = $hash;
            // NULLで保存できないので一旦から文字を入れる(この後マスターの情報を別途追加する)
            $releaseControl->release_at = '2023-11-20 00:00:00';
            $releaseControl->client_data_hash = "";
            $releaseControl->client_opr_data_hash = "";
        }
        // jsonファイルの書き込み
        if ($target === "mstI18n" || $target === "oprI18n") {
            foreach ($languages as $language) {
                if ($target === "mstI18n") {
                    $fileName = "mst_i18n_{$language}_{$hash}.json.tmp";
                    $filePath = "masteri18ndata/" . $fileName;
                } elseif ($target === "oprI18n") {
                    $fileName = "opr_i18n_{$language}_{$hash}.json.tmp";
                    $filePath = "operationi18ndata/" . $fileName;
                } else {
                    throw new \Exception('インポート対象が指定されていません');
                }
                $this->writeEncrypted($serializedFileDirPath, $releaseKey, $hash, $filePath, $json[$language]);
                //TODO decryptedファイルの生成_暗号化処理を復元後にコメントを外す
//          $version = [$releaseKey, $hash];
//          $masterEncryptionSetting = MasterEncryptionSetting::create((int)$releaseKey, $hash);
//          if (!$masterEncryptionSetting->createEncryptedOnly()) {
//             $this->writeDecrypted($serializedFileDirPath, $releaseKey, $hash, $fileName, $json);
//          }
//          if (!in_array($version, $versions)) $versions[] = $version;
                $clientDataHash = $this->createHashFromFile($serializedFileDirPath, $filePath);
                if ($target === "mstI18n") {
                    $newFilePath = "masteri18ndata/" . "mst_i18n_{$language}_{$clientDataHash}.json";
                    $releaseControl->setClientI18nDataHash($language, $clientDataHash);
                } elseif ($target === "oprI18n") {
                    $newFilePath = "operationi18ndata/" . "opr_i18n_{$language}_{$clientDataHash}.json";
                    $releaseControl->setClientOprI18nDataHash($language, $clientDataHash);
                } else {
                    // 一旦マスター投入を設定
                    $newFilePath = "masteri18ndata/" . "mst_i18n_{$language}_{$clientDataHash}.json";
                }
                rename("{$serializedFileDirPath}/{$filePath}", "{$serializedFileDirPath}/{$newFilePath}");
            }
        } else {
            if ($target === "mst") {
                $fileName = "masterdata_{$hash}.json.tmp";
                $filePath = "masterdata/" . $fileName;
            }
            elseif ($target === "opr") {
                $fileName = "operationdata_{$hash}.json.tmp";
                $filePath = "operationdata/" . $fileName;
            }
            $this->writeEncrypted($serializedFileDirPath, $releaseKey, $hash, $filePath, $json);
            //TODO decryptedファイルの生成_暗号化処理を復元後にコメントを外す
//        $version = [$releaseKey, $hash];
//        $masterEncryptionSetting = MasterEncryptionSetting::create((int)$releaseKey, $hash);
//        if (!$masterEncryptionSetting->createEncryptedOnly()) {
//            $this->writeDecrypted($serializedFileDirPath, $releaseKey, $hash, $fileName, $json);
//        }
//        if (!in_array($version, $versions)) $versions[] = $version;

            $clientDataHash = $this->createHashFromFile($serializedFileDirPath, $filePath);
            if ($target === "mst") {
                $newFilePath = "masterdata/" . "masterdata_{$clientDataHash}.json";
                $releaseControl->client_data_hash = $clientDataHash;
            } elseif ($target === "opr") {
                $releaseControl->client_opr_data_hash = $clientDataHash;
                $newFilePath = "operationdata/" . "operationdata_{$clientDataHash}.json";
            } else {
                // 一旦マスター投入を設定
                $newFilePath = "masterdata/" . "masterdata_{$clientDataHash}.json";
            }
            rename("{$serializedFileDirPath}/{$filePath}", "{$serializedFileDirPath}/{$newFilePath}");
        }
        $releaseControl->save();
        return [$releaseKey, $hash];
    }

    /**
     * CSVからクライアントへのシリアライズを除外するシート名を取得する
     * @return array
     */
    public function loadExcludeSheetNamesFromCsv() : array {
        $settingCsvPath = config('admin.spreadSheetCsvDir') . '/' . config('admin.serializeDataOutputSettingSheetName') . '.csv';
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
     * @param string $serializedFileDirPath 出力先のルートディレクトリ
     * @param string $releaseKey リリースキー
     * @param string $hash ハッシュ
     * @param string $fileName 出力先のファイル名
     * @param string $data 出力対象のデータ
     * @return void
     */
    private function writeEncrypted(string $serializedFileDirPath, string $releaseKey, string $hash, string $filePath, array $data): void
    {
        $masterEncryptionSetting = MasterEncryptionSetting::create((int)$releaseKey, $hash);
        $password = $masterEncryptionSetting->getPassword();
        $salt = $masterEncryptionSetting->getSalt();
        // TODO: クライアント側の暗号化対応終にコメントアウトを外す
        // $encryptedData = $this->cryptography->encrypt(json_encode($data), $password, $salt);
        $encryptedData = $data;
        $this->fileOperator->write($serializedFileDirPath.'/'.$filePath, $encryptedData);
    }

    /**
     * 暗号化なしでファイルを生成する
     * @param string $serializedFileDirPath 出力先のルートディレクトリ
     * @param string $releaseKey リリースキー
     * @param string $hash ハッシュ
     * @param string $fileName 出力先のファイル名
     * @param array $data 出力対象のデータ
     * @return void
     */
    private function writeDecrypted(string $serializedFileDirPath, string $releaseKey, string $hash, string $fileName, array $data): void
    {
        $this->fileOperator->write($serializedFileDirPath.'/decrypted/'.$releaseKey.'/'.$hash.'/'.$fileName, $data);
    }
}
