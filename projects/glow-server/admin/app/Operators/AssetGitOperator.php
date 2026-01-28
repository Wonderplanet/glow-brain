<?php

namespace App\Operators;

use App\Constants\AssetConstant;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\Commit;
use CzProject\GitPhp\GitRepository;
use Illuminate\Support\Facades\Log;

class AssetGitOperator
{

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
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
        $this->sparseClone($branch);
    }

    public function isCloned(): bool
    {
        return file_exists($this->storagePath . '/.git');
    }

    private function sparseClone($branch = null): void
    {
        ini_set('max_execution_time', AssetConstant::ASSET_IMPORT_MAX_EXECUTION_TIME);

        if (!$this->isCloned()) {
            Log::info("アセット専用の軽量sparse cloneを開始します（--depth 1, --filter=blob:none, --sparse）: {$this->repositoryUrl}");

            $commands = [
                '--depth',
                '1',
                '--filter=blob:none',
                '--sparse',
                '-q'
            ];

            if (!is_null($branch)) {
                $commands[] = '--branch';
                $commands[] = $branch;
            }

            $this->git->cloneRepository($this->repositoryUrl, $this->storagePath, $commands);

            // sparse-checkoutをconeモードで設定
            $repo = $this->getRepo();
            $repo->execute('sparse-checkout', 'init', '--cone');
            $repo->execute('sparse-checkout', 'set', ...AssetConstant::ASSET_PATHS);

            Log::info("sparse clone完了（coneモード）。アセットディレクトリ対象: " . implode(', ', AssetConstant::ASSET_PATHS));
        }
    }

    public function getCurrentBranchName(): string
    {
        return $this->getRepo()->getCurrentBranchName();
    }

    public function branches(): array
    {
        return $this->getRepo()->getBranches();
    }

    /**
     * リモートブランチ一覧を取得（最新情報をfetch後）
     *
     * @return array ブランチ名の配列（origin/プレフィックスなし）
     */
    public function getRemoteBranches(): array
    {
        // shallow cloneでfetch設定が特定ブランチに制限されている場合があるため、
        // 明示的に全てのリモートブランチをfetchする
        $this->getRepo()->execute('fetch', AssetConstant::GIT_ORIGIN, '+refs/heads/*:refs/remotes/origin/*', '--prune');

        // リモートブランチ一覧を取得
        $result = $this->getRepo()->execute('branch', '-r');

        $branches = [];
        foreach ($result as $branch) {
            $branch = trim($branch);

            // HEADシンボリック参照（例: origin/HEAD -> origin/main）を除外
            if (str_contains($branch, '->')) {
                continue;
            }

            // "origin/" プレフィックスを削除
            if (str_starts_with($branch, AssetConstant::GIT_ORIGIN . '/')) {
                $branchName = substr($branch, strlen(AssetConstant::GIT_ORIGIN) + 1);
                $branches[] = $branchName;
            }
        }

        return $branches;
    }

    public function resetToHead(): void
    {
        $this->getRepo()->execute('reset', '--hard', 'HEAD');
        $this->getRepo()->execute('clean', '-fd');
    }

    public function resetToOrigin(string $branch = null): void
    {
        $branch = empty($branch) ? $this->getCurrentBranchName() : $branch;
        $this->getRepo()->execute('reset', '--hard', AssetConstant::GIT_ORIGIN . '/' . $branch);
        $this->getRepo()->execute('clean', '-fd');
    }

    public function checkout($branch): void
    {
        $this->getRepo()->fetch(AssetConstant::GIT_ORIGIN);

        // sparse-checkoutの設定を確認・更新（ブランチ切り替え前）
        $this->updateSparseCheckout();

        // ローカルブランチが存在するかチェック
        $localBranches = $this->getRepo()->getBranches();
        $isLocalBranchExists = in_array($branch, $localBranches, true);

        if ($isLocalBranchExists) {
            // ローカルブランチが存在する場合は通常のcheckout
            $this->getRepo()->checkout($branch);
        } else {
            // ローカルブランチが存在しない場合は、リモート追跡ブランチからcheckout
            // これにより、新しいローカルブランチが自動的に作成される
            $this->getRepo()->execute('checkout', '-B', $branch, AssetConstant::GIT_ORIGIN . '/' . $branch);
        }

        // sparse-checkoutの設定を再確認（ブランチ切り替え後）
        $this->updateSparseCheckout();
    }

    public function hasChanges(): bool
    {
        $this->getRepo()->fetch(AssetConstant::GIT_ORIGIN);
        return $this->getRepo()->hasChanges();
    }

    public function fetch(): bool
    {
        $this->getRepo()->fetch(AssetConstant::GIT_ORIGIN);
        return true;
    }

    public function fetchUpdateLocalBranch(): bool
    {
        $this->getRepo()->fetch(AssetConstant::GIT_ORIGIN, ['-p']);
        return true;
    }

    public function pull(?string $branch = null): void
    {
        if (!empty($branch)) {
            // リモートブランチ情報を最新化
            $this->fetchUpdateLocalBranch();

            // ブランチ切り替え前に、作業ディレクトリをクリーンにする
            $this->resetToHead();

            // ローカルブランチが存在するかチェック
            $localBranches = $this->getRepo()->getBranches();
            $isLocalBranchExists = in_array($branch, $localBranches, true);

            if ($isLocalBranchExists) {
                // ローカルブランチが存在する場合は通常のcheckout
                $this->getRepo()->checkout($branch);
            } else {
                // ローカルブランチが存在しない場合は、リモート追跡ブランチからcheckout
                // これにより、新しいローカルブランチが自動的に作成される
                $this->getRepo()->execute('checkout', '-B', $branch, AssetConstant::GIT_ORIGIN . '/' . $branch);
            }

            // sparse-checkoutの設定を確認・更新
            $this->updateSparseCheckout();

            // 指定したブランチの最新状態に強制的に合わせる
            Log::info("アセットファイルの取り込みを実行します: {$branch}");
            $this->resetToOrigin($branch);
            Log::info("アセットファイルの取り込み完了");
        } else {
            // ブランチ指定がない場合は通常のpull
            $this->updateSparseCheckout();
            Log::info("アセットファイルのpullを実行します");
            $this->getRepo()->pull(AssetConstant::GIT_ORIGIN);
            Log::info("アセットファイルのpull完了");
        }
    }

    /**
     * sparse-checkoutの設定を最新の対象パスに更新（coneモード）
     */
    private function updateSparseCheckout(): void
    {
        try {
            $repo = $this->getRepo();

            // 既にconeモードが有効かチェック
            try {
                $config = $repo->execute('config', 'core.sparseCheckoutCone');
                $isConeMode = !empty($config) && $config[0] === 'true';
            } catch (\Throwable $e) {
                $isConeMode = false;
            }

            if (!$isConeMode) {
                $repo->execute('sparse-checkout', 'init', '--cone');
                Log::info("sparse-checkoutをconeモードに変更");
            }

            $repo->execute('sparse-checkout', 'set', ...AssetConstant::ASSET_PATHS);
            Log::info("sparse-checkout設定を更新（coneモード）: " . implode(', ', AssetConstant::ASSET_PATHS));
        } catch (\Throwable $e) {
            Log::warning("sparse-checkout設定の更新に失敗: " . $e->getMessage());
        }
    }

    public function getBranchHeadCommitId($branch = null): string
    {
        $branch = empty($branch) ? "HEAD" : $branch;
        $result = $this->getRepo()->execute("rev-parse", $branch);
        if (count($result) > 0) return $result[0];
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
            'asset_paths' => AssetConstant::ASSET_PATHS,
        ];
    }

}
