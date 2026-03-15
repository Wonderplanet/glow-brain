<?php

declare(strict_types=1);

namespace App\Entities\Datalake;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;

abstract class DatalakeFileWriter
{
    protected int $part = 0;
    protected string $tableName;
    protected CarbonImmutable $targetDate;
    protected int $fileSizeLimit;
    protected string $disk;
    protected string $fileName;
    protected mixed $handle;

    public function __construct(string $tableName, CarbonImmutable $targetDate, string $disk, int $fileSizeLimit)
    {
        $this->tableName = $tableName;
        $this->targetDate = $targetDate;
        $this->disk = $disk;
        $this->fileSizeLimit = $fileSizeLimit;

        $this->openNewFile();
    }

    abstract protected function generateFileName(): string;

    /**
     * Jsonを書き込み、ファイル更新が入ったらtrueを返す
     * usr/logのmlr変換と同じ挙動にするため、すべての値を文字列に変換する
     * @param array $data
     * @return bool
     */
    public function writeJson(array $data): bool
    {
        $stringifiedData = $this->stringifyAllValues($data);
        $json = json_encode($stringifiedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->writeRaw($json);
    }

    /**
     * すべての値を文字列に変換する（usr/logのmlr --jvquoteallと同じ挙動）
     * - null → 空文字列
     * - 配列 → JSON文字列
     * - bool → "true"/"false"
     * - その他 → 文字列キャスト
     * @param array $data
     * @return array<string, string>
     */
    private function stringifyAllValues(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $result[$key] = '';
            } elseif (is_array($value)) {
                $result[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (is_bool($value)) {
                $result[$key] = $value ? 'true' : 'false';
            } else {
                $result[$key] = (string)$value;
            }
        }
        return $result;
    }

    /**
     * ファイルにデータを書き込み、ファイル更新が入ったらtrueを返す
     * @param string $data
     * @return bool
     */
    public function writeRaw(string $data): bool
    {
        fwrite($this->handle, "{$data}\n");
        if (ftell($this->handle) > $this->fileSizeLimit) {
            $this->finalize();
            $this->part++;
            $this->openNewFile();
            return true;
        }
        return false;
    }

    /**
     * 新しいファイルを開く
     */
    protected function openNewFile(): void
    {
        $this->fileName = $this->generateFileName();
        $path = Storage::disk($this->disk)->path($this->fileName);
        $this->handle = fopen($path, 'a');
    }

    /**
     * ファイルを閉じる
     */
    public function finalize(): void
    {
        // ファイルの最後にカンマを削除
        fseek($this->handle, -1, SEEK_END);
        $pos = ftell($this->handle);
        ftruncate($this->handle, $pos);
        fclose($this->handle);
    }

    /**
     * ファイル名を取得する
     * @return string
     */
    public function getCurrentFileName(): string
    {
        return $this->fileName;
    }
}

