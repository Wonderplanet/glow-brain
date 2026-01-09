<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\Commit;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GitOperator
{
    const ORIGIN = 'origin';
    private readonly string $storagePath;
    private readonly string $repositoryUrl;

    private $git;
    private $repo;

    public function __construct(string $repositoryUrl, string $storagePath)
    {
        $this->storagePath = $storagePath;
        $this->repositoryUrl = $repositoryUrl;

        $this->git = new Git;
    }

    private function getRepo(): GitRepository
    {
        if (is_null($this->repo)) $this->repo = $this->git->open($this->storagePath);
        return $this->repo;
    }

    public function setupDirectory($branch = null): void
    {
        if (!file_exists($this->storagePath))
        {
            mkdir($this->storagePath);
        }
        $this->clone($branch);
    }

    public function isCloned() : bool
    {
        return count(glob($this->storagePath . "/*")) > 0;
    }

    private function clone($branch = null) : void
    {
        if (!$this->isCloned())
        {
            if (is_null($branch)) {
                $this->git->cloneRepository($this->repositoryUrl, $this->storagePath, ["-q"]);
            } else {
                $this->git->cloneRepository($this->repositoryUrl, $this->storagePath, ["-q", "--branch", $branch]);
            }
        }
    }

    public function branches() : array
    {
        return $this->getRepo()->getBranches();
    }

    public function resetToHead() : void
    {
        $this->getRepo()->execute('reset', '--hard', 'HEAD');
        $this->getRepo()->execute('clean', '-fd'); // untrackedも戻す
    }
    public function resetToOrigin(string $branch = null) : void
    {
        $branch = empty($branch) ? $this->getRepo()->getCurrentBranchName() : $branch;
        $this->getRepo()->execute('reset', '--hard', 'origin/'.$branch);
        $this->getRepo()->execute('clean', '-fd'); // untrackedも戻す
    }

    public function checkout($branch): void
    {
        $this->getRepo()->fetch(self::ORIGIN);
        $this->getRepo()->checkout($branch);
    }

    // Untrackedも含め、変更があるかどうか
    public function hasChanges(): bool
    {
        $this->getRepo()->fetch(self::ORIGIN);
        return $this->getRepo()->hasChanges();
    }

    /**
     * 下記のようなdiff結果を返す
        diff --git a/PictureBookReward.csv b/PictureBookReward.csv
        index 13cc4db..d0f8efb 100644
        --- a/PictureBookReward.csv
        +++ b/PictureBookReward.csv
        @@ -7 +7 @@
        -,,ENABLE,id,releaseKey,pictureBookId,nameTextId,descriptionTextId,requiredInvestRate,rewardType,itemType,itemId,itemNum,bonusType,bonusNum
        +,,ENABLE,id,releaseKey,pictureBookId,nameTextId,descriptionTextId,requiredSurveyRate,rewardType,itemType,itemId,itemNum,bonusType,bonusNum
     * @return array
     * @throws \CzProject\GitPhp\GitException
     */
    public function diff($hash, $options = []): array
    {
        $command = array_merge(['diff', $hash, '-U0', '--no-color'], $options);
        return $this->getRepo()->execute($command);
    }

    /**
     * toHashとfromHashで比較し、toHashにはない差分を返す
     * 結果はdiffメソッドと同様
     *
     * @param string $toHash
     * @param string $formHash
     * @param array $options
     * @return array
     * @throws GitException
     */
    public function diffFromHash(string $toHash, string $formHash, array $options = []): array
    {
        $this->getRepo()->fetch(self::ORIGIN);
        $command = array_merge(['diff', $toHash, $formHash, '-U0', '--no-color'], $options);
        return $this->getRepo()->execute($command);
    }

    public function commitAll($message): bool
    {
        if (!$this->hasChanges()) return false;

        $this->getRepo()->addAllChanges();
        $this->getRepo()->commit($message);
        return true;
    }

    public function resolveConflictFromOrigin($branch): bool
    {
        $currentBranch = $this->getRepo()->getCurrentBranchName();
        $inBranch = !empty($currentBranch) && ($currentBranch === $branch);
        if (!$inBranch) {
            // ブランチにいない場合は、一時ブランチを作成し、そこにoriginをmergeして、その結果にbranchを合わせてpushする
            $tmpBranch = 'tmp_branch';
            try {
                $this->getRepo()->execute('branch', '-D', $tmpBranch);
            } catch (GitException $e) {
                // 一時ブランチはなくてもエラーにしない
                Log::info($e->getMessage());
            }
            $this->getRepo()->execute('checkout', '-b', $tmpBranch);
            $this->getRepo()->execute('fetch');
            $this->getRepo()->execute('merge', self::ORIGIN.'/'.$branch, '-X', 'ours');
            $this->checkout($branch);
            $this->getRepo()->execute('reset', '--hard', $tmpBranch);
        } else {
            // ブランチにいれば、そのブランチでoriginにmerge
            $this->getRepo()->execute('fetch');
            $this->getRepo()->execute('merge', self::ORIGIN.'/'.$branch, '-X', 'ours');
        }
        return true;
    }

    public function push(): bool
    {
        $this->getRepo()->push(self::ORIGIN);
        return true;
    }

    public function fetch(): bool
    {
        $this->getRepo()->fetch(self::ORIGIN);
        return true;
    }

    public function fetchUpdateLocalBranch(): bool
    {
        $this->getRepo()->fetch(self::ORIGIN, ['-p']);
        return true;
    }

    public function pull(?string $branch = null) : void
    {
        if(!empty($branch)) $this->repo->checkout($branch);
        $this->getRepo()->pull(self::ORIGIN);
    }

    public function getBranchHeadCommitId($branch = null): string
    {
        $branch = empty($branch) ? "HEAD" : $branch;
        $result = $this->getRepo()->execute("rev-parse", $branch);
        if(count($result) > 0) return $result[0];
        return "";
    }

    public function getLastCommitId(): string
    {
        return $this->getRepo()->getLastCommitId()->toString();
    }

    public function getLastCommit(): Commit
    {
        $commitId = $this->getRepo()->getLastCommitId();
        $commit = $this->getRepo()->getCommit($commitId);
        return $commit;
    }

    // Untracked filesをインデックスに追加する
    public function addUntrackedFiles(): void
    {
        $this->getRepo()->execute('add', '-N', '.');
    }

    /**
     * 指定したコミットハッシュでチェックアウトブランチを実行する
     *
     * @param string $commitHash
     * @param string $newBranch
     * @return void
     * @throws GitException
     */
    public function checkoutFromCommitHash(string $commitHash, string $newBranch): void
    {
        $this->getRepo()->execute('checkout', '-b', $newBranch, $commitHash);
    }

    /**
     * 指定したブランチを削除する
     *
     * @param string $branch
     * @return void
     * @throws GitException
     */
    public function deleteBranch(string $branch): void
    {
        $this->getRepo()->execute('branch', '-D', $branch);
    }

    /**
     * .envに設定したGIT_BRANCH名を取得する
     * 設定がなければエラーとする
     *
     * @return string
     * @throws \Exception
     */
    public function getGitBranch(): string
    {
        $gitBranch = config('wp_master_asset_release_admin.gitBranch');
        if (is_null($gitBranch)) {
            // .envにGIT_BRANCHの設定がなければエラーとする
            throw new \Exception('undefined GIT_BRANCH from envFile');
        }

        return $gitBranch;
    }

    /**
     * Gitエラーの通知を実行する
     * 
     * @param GitException $e
     */
    public function sendGitErrorLogAndNotification(GitException $e)
    {
        Log::error('', [$e]);

        $runnerResult = $e->getRunnerResult();
        if ($runnerResult && $runnerResult->getOutput()) {
            Log::error($runnerResult->getOutput());
        }

        Notification::make()
            ->title('Gitエラー')
            ->body('gitコマンドエラーが起きました。laravel.logを確認してください。')
            ->danger()
            ->persistent()
            ->send();
    }
}
