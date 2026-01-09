<?php

namespace App\Services\MasterData;

use App\Operators\GitOperator;

class GitCommitService
{
    private $git = null;

    /**
     * Git管理スプレッドシートCSVのディレクトリを初期化する
     * @return void
     */
    public function initialize(): void
    {
        $git = $this->getGitOperator();
        if (!$git->isCloned()) {
            $git->setupDirectory(env('GIT_BRANCH'));
        }
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリをコミットする
     * @param string $message
     * @return bool
     */
    public function commitSpreadSheetCsv(string $message): bool
    {
        $git = $this->getGitOperator();
        return $git->commitAll($message);
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリをリモートにプッシュする
     * @return void
     */
    public function pushSpreadSheetCsv(): void
    {
        $git = $this->getGitOperator();
        $git->resolveConflictFromOrigin(env('GIT_BRANCH'));
        $git->push();
    }

    /**
     * Git管理スプレッドシートCSVのディレクトリを設定中のブランチにリセットする
     * @return void
     */
    public function resetSpreadSheetCsv(): void
    {
        $git = $this->getGitOperator();
        $git->fetch();
        $git->resetToOrigin(env('GIT_BRANCH'));
    }

    /**
     * Gitの現在のハッシュを取得する
     * @return string
     */
    public function getCurrentHash(): string
    {
        $git = $this->getGitOperator();
        return substr($git->getLastCommitId(), 0, 16);
    }
    /**
     * 操作対象のブランチのコミットハッシュを取得する
     * @return string
     */
    public function getBranchHeadHash(): string
    {
        $git = $this->getGitOperator();
        $git->fetch();
        return $git->getBranchHeadCommitId(env('GIT_BRANCH'));
    }

    /**
     * 現在の取り込み状態を取り消して、指定したブランチにチェックアウトする
     * @param string $branch
     * @return void
     */
    public function checkoutBranch(string $branch)
    {
        $git = $this->getGitOperator();
        $git->resetToHead();
        $git->checkout($branch);

        $git->resetToOrigin($branch);
    }

    /**
     * 現在の取り込み状態を取り消して、指定したハッシュにチェックアウトする
     * @param string $hash
     * @return void
     */
    public function checkoutHash(string $hash)
    {
        $git = $this->getGitOperator();
        $git->resetToHead();
        $git->checkout($hash);
    }

    /**
     * ブランチ一覧を取得する
     *
     * @return array
     */
    public function getBranches(): array
    {
        $git = $this->getGitOperator();
        $git->fetchUpdateLocalBranch();
        $branches = $git->branches();

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

    private function getGitOperator(): GitOperator
    {
        if (is_null($this->git)) {
            $this->git = new GitOperator(config('admin.repositoryUrl'), config('admin.spreadSheetCsvDir'));
        }
        return $this->git;
    }
}
