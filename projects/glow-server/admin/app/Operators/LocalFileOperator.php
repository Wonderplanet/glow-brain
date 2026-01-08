<?php

namespace App\Operators;

class LocalFileOperator
{
    /**
     * 指定パスにディレクトリを作成する
     * 既に存在する場合は何もしない
     *
     * @param string $directoryPath
     * @return void
     */
    public function createDir(string $directoryPath): void
    {
        // ディレクトリのパスであっても、is_dirがfalseを返す場合があったため、file_existsのみで判定しています
        if (file_exists($directoryPath)) {
            return;
        }

        mkdir($directoryPath, 0755, true); // recursive
    }

    /**
     * 指定パスにファイルを書き込む
     * ディレクトリがない場合は作成する
     *
     * @param string $putFilePath
     * @param mixed $data
     * @return void
     */
    public function putWithCreateDir(string $putFilePath, mixed $data): void
    {
        $this->createDir(dirname($putFilePath));

        file_put_contents($putFilePath, $data);
    }

    public function deleteFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
