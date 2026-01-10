<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils;

use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\ImportDataDiffEntity;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Utils\MasterDataUtility;

/**
 * マスターデータインポートツールv2で使用するUtilityクラス
 */
class MasterDataImportUtility
{
    /**
     * mng_master_release_versions.data_hashを生成する
     * リリースキーごとのdata_hashを返す
     *
     * @param array $releaseKeys
     * @param array $masterDataHashMap
     * @param array $masterDataI18nHashMap
     * @param array $operationDataHashMap
     * @param array $operationDataI18nHashMap
     * @param array $serverDbHashMap
     * @return array<string, string>
     */
    public static function generateDataHashMapByHashMap(
        array $releaseKeys,
        array $masterDataHashMap,
        array $masterDataI18nHashMap,
        array $operationDataHashMap,
        array $operationDataI18nHashMap,
        array $serverDbHashMap,
    ): array
    {
        $dataHashMap = [];
        
        foreach ($releaseKeys as $releaseKey) {
            $masterDataHash = $masterDataHashMap[$releaseKey];
            $operationDataHash = $operationDataHashMap[$releaseKey];
            $serverDbHash = $serverDbHashMap[$releaseKey];
            // i18nのマップはさらに言語ごとに分かれているのでそれぞれ抽出
            $masterDataHashI18nList = $masterDataI18nHashMap[$releaseKey];
            $masterDataHashI18nJa = $masterDataHashI18nList['ja'];
            $operationDataI18nList = $operationDataI18nHashMap[$releaseKey];
            $operationDataI18nJa = $operationDataI18nList['ja'];
            
            // 全て文字結合してmd5を生成
            $mergedHash = $masterDataHash
                . $masterDataHashI18nJa
                . $operationDataHash
                . $operationDataI18nJa
                . $serverDbHash;
            $dataHashMap[$releaseKey] = md5($mergedHash);
        }
        
        return $dataHashMap;
    }
    
    /**
     * マスターデータの1レコードのパラメータ内で、改行された値を改行コードに変換する
     * 主にスプシからmasterdata_csvのcsvファイルを生成する際に使用
     *
     * @param array $row
     * @return array
     */
    public static function convertToLineBreaksFromSpreadSheetRow(array $row): array
    {
        return array_map(function ($column) {
            // $column内で改行されていた場合は \n に置換
            return is_string($column) ? str_replace(PHP_EOL, '\n', $column) : $column;
        }, $row);
    }
    
    /**
     * マスターデータの1レコードのパラメータ内で、改行コードがあればPHP_EOLで改行する
     * 主にクライアントjsonファイル生成で使用
     *
     * @param array $row
     * @return array
     */
    public static function convertToSystemEOLFromDatabaseCsv(array $row): array
    {
        return array_map(function ($column) {
            // $column内の改行コード(\n)を改行する
            return is_string($column) ? str_replace('\n', PHP_EOL, $column) : $column;
        }, $row);
    }
    
    /**
     * 差分確認画面表示用に差分データのソートを実行
     * ソート内容
     *  1.ステータスが削除、新規、変更の順(テンプレート側で対応済み)
     *  2.release_keyの昇順
     *  3.idの昇順(アルファベット順)
     *
     * @param array<ImportDataDiffEntity> $diffs
     * @return array
     */
    public static function sortDiffData(array $diffs): array
    {
        // deleteDataとnewData用のソートコールバック関数
        $sortFunc = function ($target) {
            usort($target, function ($a, $b) {
                // release_keyの昇順比較
                $aReleaseKey = $a['beforeRow']['release_key'] ?? 0;
                $bReleaseKey = $b['beforeRow']['release_key'] ?? 0;
                $releaseKeyComparison = $aReleaseKey <=> $bReleaseKey;
                if ($releaseKeyComparison !== 0) {
                    return $releaseKeyComparison;
                }
                // idの昇順比較
                return $a['id'] <=> $b['id'];
            });
            
            return $target;
        };
        
        return collect($diffs)->map(function (ImportDataDiffEntity $entity) use ($sortFunc) {
            $entityArray = $entity->toArray();
            // 変更行データのソートを実行
            $modifyData = $entityArray['modifyData'];
            usort($modifyData, function ($a, $b) {
                // release_keyの昇順比較
                $aReleaseKey = $a['beforeRow']['release_key'] ?? 0;
                $bReleaseKey = $b['beforeRow']['release_key'] ?? 0;
                $releaseKeyComparison = $aReleaseKey <=> $bReleaseKey;
                if ($releaseKeyComparison !== 0) {
                    return $releaseKeyComparison;
                }
                // idの昇順比較
                return $a['beforeRow']['id'] <=> $b['beforeRow']['id'];
            });
            $entityArray['modifyData'] = $modifyData;
            
            // 削除行のソート
            $deleteData = $entityArray['deleteData'];
            $entityArray['deleteData'] = $sortFunc($deleteData);
            
            // 新規行のソート
            $newData = $entityArray['newData'];
            $entityArray['newData'] = $sortFunc($newData);
            
            return $entityArray;
        })->toArray();
    }
    
    /**
     * 差分確認画面 確認モーダル表示用 各マスターデータの変更、新規追加、削除件数をreleaseKeyごとに分類して集計したマップを生成して返す
     *
     * @param array $entities
     * @return array
     */
    public static function makeRowCountMapFromAllTables(array $entities): array
    {
        $modifyRowCountMapFromAllTable = [];
        $newRowCountMapFromAllTable = [];
        $deleteRowCountMapFromAllTable = [];
        
        foreach ($entities as $entityArray) {
            // 変更行の集計
            $modifyRowCountMapByReleaseKey = $entityArray['modifyRowCountMapByReleaseKey'];
            foreach ($modifyRowCountMapByReleaseKey as $releaseKey => $rowCount) {
                if (!isset($modifyRowCountMapFromAllTable[$releaseKey])) {
                    $modifyRowCountMapFromAllTable[$releaseKey] = 0;
                }
                $modifyRowCountMapFromAllTable[$releaseKey] += $rowCount;
            }
            
            // 追加行の集計
            $newRowCountMapByReleaseKey = $entityArray['newRowCountMapByReleaseKey'];
            foreach ($newRowCountMapByReleaseKey as $releaseKey => $rowCount) {
                if (!isset($newRowCountMapFromAllTable[$releaseKey])) {
                    $newRowCountMapFromAllTable[$releaseKey] = 0;
                }
                $newRowCountMapFromAllTable[$releaseKey] += $rowCount;
            }
            
            // 削除行の集計
            $deleteRowCountMapByReleaseKey = $entityArray['deleteRowCountMapByReleaseKey'];
            foreach ($deleteRowCountMapByReleaseKey as $releaseKey => $rowCount) {
                if (!isset($deleteRowCountMapFromAllTable[$releaseKey])) {
                    $deleteRowCountMapFromAllTable[$releaseKey] = 0;
                }
                $deleteRowCountMapFromAllTable[$releaseKey] += $rowCount;
            }
        }
        
        return [$modifyRowCountMapFromAllTable, $newRowCountMapFromAllTable, $deleteRowCountMapFromAllTable];
    }
    
    /**
     * @param array $mngMasterReleaseVersion
     * @return array
     */
    public static function getMasterDataHashPathList(array $mngMasterReleaseVersion): array
    {
        // .dataファイルパス取得
        $mstHashPath = MasterDataUtility::getPath(MasterData::MASTERDATA, $mngMasterReleaseVersion['client_mst_data_hash']);
        $mstI18nJaHashPath = MasterDataUtility::getI18nPath(
            MasterData::MASTERDATA_I18N_PATH,
            MasterData::MASTERDATA_I18N,
            Language::Ja,
            $mngMasterReleaseVersion['client_mst_data_i18n_ja_hash']
        );
        $oprHashPath = MasterDataUtility::getPath(
            MasterData::OPERATIONDATA,
            $mngMasterReleaseVersion['client_opr_data_hash']
        );
        $oprI8nJaPath = MasterDataUtility::getI18nPath(
            MasterData::OPERATIONDATA_I18N_PATH,
            MasterData::OPERATIONDATA_I18N,
            Language::Ja,
            $mngMasterReleaseVersion['client_opr_data_i18n_ja_hash']
        );

        return [
            $mstHashPath,
            $mstI18nJaHashPath,
            $oprHashPath,
            $oprI8nJaPath,
        ];
    }
    
    /**
     * 環境間インポート用 s3のmysqldumpファイルの接頭辞を取得する
     *
     * @param $fromEnvironment
     * @return string
     */
    public static function getS3MysqlDumpFilePrefix($fromEnvironment): string
    {
        $prefixMap = config('wp_master_asset_release_admin.master_data_mysqldump_file_prefix', []);
        
        if (!isset($prefixMap[$fromEnvironment])) {
            // configに設定されてない環境の場合は指定環境名を返す
            return $fromEnvironment;
        }
        
        // 設定された接頭辞を返す
        return $prefixMap[$fromEnvironment];
    }
    
    /**
     * インポート元環境のmysqldumpファイル取得用s3接続情報を取得
     * config/filesystems.phpに定義している接続情報のキー名を生成している
     *
     * @param string $fromEnvironment
     * @return string
     */
    public static function getFromEnvironmentMySqlDumpConfigName(string $fromEnvironment): string
    {
        return "s3_master_dump_{$fromEnvironment}";
    }
    
    /**
     * インポート元環境のクライアント参照マスターデータファイル取得用s3接続情報を取得
     * config/filesystems.phpに定義している接続情報のキー名を生成している
     *
     * @param string $fromEnvironment
     * @return string
     */
    public static function getFromEnvironmentClientMasterDataConfigName(string $fromEnvironment): string
    {
        return "s3_client_master_data_{$fromEnvironment}";
    }
}
