# Worktree作成時にremoteの最新を取り込む実装計画

## 目的
Slackメンションでブランチ指定時、remoteの最新状態を取り込んでからworktreeを作成する

## 変更対象ファイル
- `src/worktree/manager.py`

## 実装内容

### `create_worktree`メソッドの修正 (36-89行目)

```python
def create_worktree(
    self,
    session_id: str,
    branch: Optional[str] = None,
) -> Path:
    worktree_path = self.base_path / f"session_{session_id}"
    branch = branch or self.default_branch

    if worktree_path.exists():
        logger.warning(...)
        return worktree_path

    try:
        # 1. remoteの最新を取得
        self._fetch_branch(branch)

        # 2. origin/<branch>からworktreeを作成
        result = subprocess.run(
            [
                "git",
                "-C",
                str(self.source_repo),
                "worktree",
                "add",
                "--detach",
                str(worktree_path),
                f"origin/{branch}",  # ← ここを変更
            ],
            ...
        )
        ...
```

### 新規メソッド追加: `_fetch_branch`

```python
def _fetch_branch(self, branch: str) -> None:
    """指定ブランチのremote最新を取得"""
    try:
        subprocess.run(
            [
                "git",
                "-C",
                str(self.source_repo),
                "fetch",
                "origin",
                branch,
            ],
            capture_output=True,
            text=True,
            check=True,
        )
        logger.info("branch_fetched", branch=branch)
    except subprocess.CalledProcessError as e:
        logger.warning("fetch_failed", branch=branch, error=e.stderr)
        # fetchが失敗してもローカルのorigin/branchで続行を試みる
```

## 検証方法

1. ローカルでSlack Botを起動
2. Slackでメンション + `branch:<ブランチ名>` を送信
3. ログで以下を確認:
   - `branch_fetched` ログが出力されること
   - `worktree_created` で `origin/<branch>` が使われていること
4. 作成されたworktreeで `git log -1` して、remoteの最新コミットと一致することを確認
