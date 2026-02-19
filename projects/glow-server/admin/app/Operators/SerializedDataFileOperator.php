<?php

namespace App\Operators;

use Illuminate\Filesystem\Filesystem;

class SerializedDataFileOperator
{
    public function cleanup($path): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory($path);
    }

    public function write(string $path, array|string $data) : void
    {
        // 書き込み先にディレクトリがなかったら作っておく
        $directoryPath = dirname($path);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true); // recursive
        }

        if (is_array($data)) {
            // arrayの場合はjsonとして書き出し
            $this->writeAsJson($path, $data);
        } else {
            // stringの場合はそのまま書き出し
            file_put_contents($path, $data);
        }
    }

    private function writeAsJson(string $path, array $data): void
    {
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
