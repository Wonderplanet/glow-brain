<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GitOperator;

/**
 * マスターデータインポートv2管理ツール用
 */
class GitCommitService
{

    private GitOperator $git;

    public function __construct()
    {
        $this->git = new GitOperator(config('wp_master_asset_release_admin.repositoryUrl'), config('wp_master_asset_release_admin.spreadSheetCsvDir'));
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリを初期化する
     * @return void
     */
    public function initialize(): void
    {
        if (!$this->git->isCloned()) {
            $this->git->setupDirectory($this->git->getGitBranch());
        }
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリをコミットする
     * @param string $message
     * @return bool
     */
    public function commitSpreadSheetCsv(string $message): bool
    {
        return $this->git->commitAll($message);
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリをリモートにプッシュする
     * @return void
     */
    public function pushSpreadSheetCsv(): void
    {
        $this->git->resolveConflictFromOrigin($this->git->getGitBranch());
        $this->git->push();
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリを設定中のブランチにリセットする
     * @return void
     */
    public function resetSpreadSheetCsv(): void
    {
        $this->git->fetch();
        $this->git->resetToOrigin($this->git->getGitBranch());
    }

    /**
     * Gitの現在のハッシュを取得する
     * @return string
     */
    public function getCurrentHash(): string
    {
        return substr($this->git->getLastCommitId(), 0, 16);
    }

    /**
     * 操作対象のブランチのコミットハッシュを取得する
     * @return string
     */
    public function getBranchHeadHash(): string
    {
        $this->git->fetch();
        return $this->git->getBranchHeadCommitId($this->git->getGitBranch());
    }

    /**
     * 現在の取り込み状態を取り消して、指定したブランチにチェックアウトする
     * @param string $branch
     * @return void
     */
    public function checkoutBranch(string $branch)
    {
        $this->git->resetToHead();
        $this->git->checkout($branch);
        $this->git->pull();
    }

    /**
     * 現在の取り込み状態を取り消して、指定したハッシュにチェックアウトする
     * @param string $hash
     * @return void
     */
    public function checkoutHash(string $hash)
    {
        $this->git->resetToHead();
        $this->git->checkout($hash);
    }

    /**
     * ブランチ一覧を取得する
     *
     * @return array
     */
    public function getBranches(): array
    {
        $this->git->fetchUpdateLocalBranch();
        $branches = $this->git->branches();

        // fadminで扱えるようにブランチ名を加工
        // 1.HEADを含むものを選択肢から削除
        // 2.remotes/originの文字を削除
        // 3.ユニーク・ソートする
        $branches = array_filter($branches, function ($branch) {
            return strpos($branch, 'HEAD') === false;
        });
        $branches = array_map(function ($branch) {
            return str_replace('remotes/origin/', '', $branch);
        }, $branches);
        $branches = array_unique($branches);
        sort($branches);

        return $branches;
    }
}
