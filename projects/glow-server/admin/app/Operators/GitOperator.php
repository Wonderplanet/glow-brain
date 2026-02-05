<?php
namespace App\Operators;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\Commit;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
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
        if (is_null($this->repo)) {
            $this->repo = $this->git->open($this->storagePath);
        };
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

    public function getCurrentBranchName() : string
    {
        return $this->getRepo()->getCurrentBranchName();
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
        $branch = empty($branch) ? $this->getCurrentBranchName() : $branch;
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

    public function commitAll($message): bool
    {
        if (!$this->hasChanges()) return false;

        $this->getRepo()->addAllChanges();
        $this->getRepo()->commit($message);
        return true;
    }

    public function resolveConflictFromOrigin($branch): bool
    {
        $currentBranch = $this->getCurrentBranchName();
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
        if(!empty($branch)) $this->getRepo()->checkout($branch);
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

    /**
     * 現在のブランチ名・最新コミットハッシュ・コミットメッセージを返す
     *
     * @return array{branch: string, hash: string, message: string}
     */
    public function getCurrentInfo(): array
    {
        $branch = $this->getCurrentBranchName();
        $hash = $this->getLastCommitId();
        $message = '';
        try {
            $commit = $this->getLastCommit();
            $message = $commit->getBody();
        } catch (\Throwable $e) {
            $message = '';
        }
        return [
            'repository' => $this->repositoryUrl,
            'branch' => $branch,
            'hash' => $hash,
            'short_hash' => substr($hash, 0, 7),
            'message' => $message,
        ];
    }
}
