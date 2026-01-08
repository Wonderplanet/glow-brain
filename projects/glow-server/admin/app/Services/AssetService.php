<?php

declare(strict_types=1);

namespace App\Services;
use App\Services\ClientGitService;
use App\Operators\S3Operator;
use Illuminate\Support\Facades\Log;
use CzProject\GitPhp\GitException;

class AssetService
{
    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private ClientGitService $clientGitService,
    ) {
    }

    public function import(?string $branch = null): void
    {
        // アセット専用のpullを実行（アセットファイルのみ更新）
        Log::info('アセット取り込みを開始します');

        try {
            $this->clientGitService->pull($branch);
        } catch (GitException $e) {
            Log::error('アセット取り込みに失敗しました', [
                'branch' => $branch,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('ブランチ「' . $branch . '」の取り込みに失敗しました。ブランチ名が正しいか確認してください。', 0, $e);
        }


        Log::info('アセット取り込みが完了しました');
    }

    /**
     * アセット取り込み情報を取得
     */
    public function getAssetInfo(): array
    {
        return $this->clientGitService->getCurrentInfo();
    }


}
