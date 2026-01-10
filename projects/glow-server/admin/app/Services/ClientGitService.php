<?php

namespace App\Services;

use App\Operators\AssetGitOperator;
use Illuminate\Support\Facades\Log;

class ClientGitService
{
    private $git = null;

    public function getCurrentInfo(): array
    {
        try {
            return $this->getGitOperator()->getCurrentInfo();
        } catch (\Throwable $e) {
            Log::error('アセット情報取得エラー: ' . $e->getMessage());
            return [];
        }
    }

    private function getGitOperator(): AssetGitOperator
    {
        if (is_null($this->git)) {
            $this->git = new AssetGitOperator(
                config('admin.clientRepositoryUrl'),
                config('admin.glowClientDir')
            );
        }

        return $this->git;
    }

    /**
     * リポジトリを手動でセットアップ
     */
    public function setupRepository(?string $branch = null): void
    {
        $git = $this->getGitOperator();

        if (!$git->isCloned()) {
            $repoUrl = config('admin.clientRepositoryUrl');
            Log::info("Gitリポジトリ（{$repoUrl}）のアセット専用軽量sparse cloneを開始します。");
            $git->setupDirectory($branch);
            Log::info("Gitリポジトリ（{$repoUrl}）のアセット専用軽量sparse cloneが完了しました。");
        }
    }

    /**
     * リポジトリがクローンされているかチェック
     */
    public function isRepositoryCloned(): bool
    {
        return $this->getGitOperator()->isCloned();
    }

    /**
     * アセットファイルの更新
     */
    public function pull(?string $branch = null)
    {
        $git = $this->getGitOperator();

        // リポジトリがクローンされていない場合は自動的にセットアップ
        if (!$git->isCloned()) {
            Log::info('リポジトリがクローンされていないため、自動的にセットアップを開始します');
            $this->setupRepository($branch);
            Log::info('リポジトリのセットアップが完了しました');
            return;
        }

        $git->pull($branch);
    }

    /**
     * ブランチ一覧を取得する（リモートブランチ）
     *
     * 最新のリモートブランチ情報を取得して返す
     */
    public function getBranches(): array
    {
        $git = $this->getGitOperator();

        // リモートブランチ一覧を取得（内部でfetchを実行）
        $branches = $git->getRemoteBranches();

        // 降順ソート
        rsort($branches);

        return $branches;
    }


}
