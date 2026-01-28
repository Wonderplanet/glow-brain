<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

/**
 * マスタデータインポートv2用
 * スプレッドシートとGit管理しているマスターデータの差分情報を持つEntity
 * 1Entityで1テーブルの情報を持つ
 */
class ImportDataDiffEntity
{
    private array $modifyData;
    private array $deleteData;
    private array $newData;

    public function __construct(
        private readonly string $sheetName,
        private readonly array $rawData,
        private readonly array $header,
        private readonly array $structureDiffAddData,
        private readonly array $structureDiffDeleteData,
        private readonly array $modifyRowCountMapByReleaseKey,
        private readonly array $newRowCountMapByReleaseKey,
        private readonly array $deleteRowCountMapByReleaseKey,
    ) {
        $this->parse();
    }

    /**
     * rawDataから変更、削除、新規データの配列を生成
     *
     * @return void
     */
    private function parse(): void
    {
        $this->modifyData = $this->rawData['modify'] ?? [];
        $this->deleteData = $this->rawData['delete'] ?? [];
        $this->newData = $this->rawData['new'] ?? [];
    }

    /**
     * 対象のシート(テーブル)名を取得
     *
     * @return string
     */
    public function getSheetName(): string
    {
        return $this->sheetName;
    }

    /**
     * シートのヘッダー(カラム)情報を取得
     *
     * @return array
     */
    public function getHeader() : array
    {
        return $this->header;
    }

    /**
     * 変更行の情報を取得
     *
     * @return array
     */
    public function getModifyData() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * $this->modifyData = [
         *   [
         *     'beforeRow' => [ 変更前の1レコードのデータ
         *       'id'=> '1',
         *       'release_key' => '20230101',
         *       'rarity' => 'Common',
         *       ...,
         *     ],
         *     'modifyColumnMap' => [ 1レコードの変更箇所を持つデータ(カラム名 => 値)
         *       'rarity' => 'Rare',
         *     ],
         *   ],
         *   ...,
         * ]
         */
        return $this->modifyData;
    }

    /**
     * 削除行の情報を取得
     *
     * @return array
     */
    public function getDeleteData() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * this->deleteData = [ 削除されたマスタデータの配列
         *   [
         *     'id' => 'ticket_test',
         *     'release_key' => '20230101',
         *     ...,
         *   ],
         *   ...,
         * ]
         */
        return $this->deleteData;
    }

    /**
     * 新規行の情報を取得
     *
     * @return array
     */
    public function getNewData() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * this->newData = [ 新規追加されたマスタデータの配列
         *   [
         *     'id' => 'new_test',
         *     'release_key' => '20230101',
         *     ...,
         *   ],
         *   ...,
         * ]
         */
        return $this->newData;
    }

    /**
     * テーブル構造の追加カラム情報を取得
     *
     * @return array<int, string>
     */
    public function getStructureDiffAddData() : array
    {
        // 追加されたカラム名の配列を返す
        return $this->structureDiffAddData;
    }

    /**
     * テーブル構造の削除カラム情報を取得
     *
     * @return array<int, string>
     */
    public function getStructureDiffDeleteData() : array
    {
        // 削除されたカラム名の配列を返す
        return $this->structureDiffDeleteData;
    }

    /**
     * release_keyごとの変更件数を取得
     * 変更が一切なければ空
     *
     * @return array<string, int>
     */
    public function getModifyRowCountMapByReleaseKey() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * this->modifyRowCountMapByReleaseKey = [
         *  202401010 => 1,
         *  ...,
         * ]
         */
        return $this->modifyRowCountMapByReleaseKey;
    }

    /**
     * release_keyごとの新規件数を取得
     * 新規が一切なければ空
     *
     * @return array<string, int>
     */
    public function getNewRowCountMapByReleaseKey() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * this->newRowCountMapByReleaseKey = [
         *  202401010 => 1,
         *  ...,
         * ]
         */
        return $this->newRowCountMapByReleaseKey;
    }

    /**
     * release_keyごとの削除件数を取得
     * 削除が一切なければ空
     *
     * @return array<string, int>
     */
    public function getDeleteRowCountMapByReleaseKey() : array
    {
        /**
         * 下記のようなデータ構造を持つ
         * this->deleteRowCountMapByReleaseKey = [
         *  202401010 => 1,
         *  ...,
         * ]
         */
        return $this->deleteRowCountMapByReleaseKey;
    }

    /**
     * 配列化した情報を取得
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'sheetName' => $this->getSheetName(),
            'header' => $this->getHeader(),
            'modifyData' => $this->getModifyData(),
            'deleteData' => $this->getDeleteData(),
            'newData' => $this->getNewData(),
            'structureDiffAddData' => $this->getStructureDiffAddData(),
            'structureDiffDeleteData' => $this->getStructureDiffDeleteData(),
            'modifyRowCountMapByReleaseKey' => $this->getModifyRowCountMapByReleaseKey(),
            'newRowCountMapByReleaseKey' => $this->getNewRowCountMapByReleaseKey(),
            'deleteRowCountMapByReleaseKey' => $this->getDeleteRowCountMapByReleaseKey(),
        ];
    }
}
