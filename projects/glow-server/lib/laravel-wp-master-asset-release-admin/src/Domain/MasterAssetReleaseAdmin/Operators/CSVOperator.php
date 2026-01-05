<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

use Illuminate\Filesystem\Filesystem;
use SplFileObject;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;

class CSVOperator
{

    public function cleanup($path): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory($path);
    }

    public function read($path) : array
    {
        $data = [];
        if (file_exists($path))
        {
            $file = new SplFileObject($path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);
            foreach ($file as $line) {
                $data[] = $line;
            }
            // SplFileObjectのデストラクタでリソース解放するのでclose不要
        }
        return $data;
    }

    public function write(string $path, array $data) : void
    {
        // 書き込み先にディレクトリがなかったら作っておく
        $directoryPath = dirname($path);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true); // recursive
        }

        $fp = new SplFileObject($path, 'w');
        foreach ($data as $row) {
            $row = MasterDataImportUtility::convertToLineBreaksFromSpreadSheetRow($row);

            $fp->fputcsv($row);
        }
        // SplFileObjectのデストラクタでリソース解放するのでclose不要
    }
}
