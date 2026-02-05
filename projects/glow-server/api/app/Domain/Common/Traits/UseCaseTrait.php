<?php

declare(strict_types=1);

namespace App\Domain\Common\Traits;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Services\DeferredTaskService;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Resource\Log\Services\LogBankService;
use App\Domain\Resource\Usr\Entities\UsrUserParameterEntity;
use App\Domain\User\Models\UsrUserParameterInterface;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Http\Responses\Data\UsrParameterData;
use App\Infrastructure\LogModelManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UseCaseで共通に使う処理をまとめたTrait
 */
trait UseCaseTrait
{
    use UsrModelManagerTrait;

    /**
     * 指定したコネクションでトランザクションを開始し、コールバックを実行する
     *
     * 複数のコネクションを指定できるよう配列でとっているのは、
     * 水平/垂直分割を想定しているため
     *
     * @param callable $callback
     * @param array<string> $connections
     * @return mixed
     */
    private function transaction(
        callable $callback,
        array $connections = [],
    ): mixed {
        // 指定が空の場合はデフォルトのコネクションを使う
        if (count($connections) === 0) {
            $connections = [config('database.default')];
        }

        // トランザクションの開始
        foreach ($connections as $connection) {
            DB::connection($connection)->beginTransaction();
        }

        // コールバックの実行
        try {
            $result = $callback();

            // トランザクションのコミット
            foreach ($connections as $connection) {
                DB::connection($connection)->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバック
            foreach ($connections as $connection) {
                DB::connection($connection)->rollBack();
            }
            throw $e;
        }
    }

    /**
     * APIリクエスト処理内で行われたユーザーデータの変更を確定させるメソッド。
     *
     * トランザクションで囲みつつ、指定されたコールバック処理を実行した後に、
     * ミッション進捗更新や、ユーザデータ・ログデータの一括保存などの処理をまとめて実行する。
     *
     * 主にPOSTメソッドなどの、ユーザーデータに更新があるAPIで使用する。
     *
     * @param callable|null $callback コールバック関数
     * UsrModelManager,LogModelManagerを使用するRepositoryであれば、一括保存処理が遅延実行できるため、ここに渡す必要はない。
     * しかし、課金(Billing),通貨(Currency)などのライブラリや、UsrDeviceRepositoryなどの、遅延実行対象外の処理がある場合は、
     * ここに渡すことで、処理をトランザクションで囲むことができる。その結果、完全なロールバックが可能になる。
     * 逆にここに渡さないと、トランザクション外で処理されるため、途中でエラーが発生すると、一部ロールバックできず、
     * データ不整合が発生してしまうので、注意。
     *
     * @param array<string> $connections
     *   true: 保存する false: 保存しない
     */
    public function applyUserTransactionChanges(
        ?callable $callback = null,
        array $connections = []
    ): mixed {

        $wrappedCallback = function () use ($callback) {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback();
            }

            // ミッション進捗判定と更新
            $this->updateMissionProgresses();

            // 1時間ごとのアクセス日時の更新
            $this->updateHourlyAccessedAtAndCreateBankActiveLog();

            // ユーザデータの一括更新
            $this->saveAll();

            return $result;
        };

        $result = $this->transaction(
            $wrappedCallback,
            $connections,
        );

        /** @var DeferredTaskService $deferredTaskService */
        $deferredTaskService = app()->make(DeferredTaskService::class);

        try {
            // DBトランザクション終了後の遅延実行タスクを実行
            $deferredTaskService->executeAfterTransactionTasks();
        } catch (\Throwable $e) {
            // トランザクション後のタスク実行例外はエラーログ出力のみ
            Log::error('Exception occurred in executeAfterTransactionTasks: ' . $e->getMessage(), ['exception' => $e]);
        }

        // ログデータの一括保存
        $this->saveAllLog();

        return $result;
    }

    /**
     * APIリクエスト処理内で行われたユーザーデータとログ変更を確定させるメソッド。
     *
     * トランザクションで囲みつつ、指定されたコールバック処理を実行した後に、
     * ユーザデータ・ミッション・ログデータの一括保存などの処理をまとめて実行する。
     *
     * アクセス日時の更新が不要な場合や、手動でこれらを制御したい場合に使用する。
     *
     * @param callable|null $callback コールバック関数
     * @param array<string> $connections
     */
    public function commitUserAndLogDataChanges(
        ?callable $callback = null,
        array $connections = []
    ): mixed {

        $wrappedCallback = function () use ($callback) {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback();
            }

            // ミッション進捗判定と更新
            $this->updateMissionProgresses();

            // ユーザデータの一括更新
            $this->saveAll();

            return $result;
        };

        $result = $this->transaction(
            $wrappedCallback,
            $connections,
        );

        // ログデータの一括保存
        $this->saveAllLog();

        return $result;
    }

    /**
     * applyUserTransactionChangesを使わないAPIでも、ログ保存やBankアクティブログ保存を行いたい場合に使用する
     */
    public function processWithoutUserTransactionChanges(): void
    {
        /**
         * 1時間ごとのアクセス日時の更新
         *
         * BankF001の判定のために1時間ごとのアクセス日時をusrDBに保存する必要がある。
         * そのため、例外的にusr_user_loginsの値変更とDB即時保存をここで行う。
         */
        $this->updateHourlyAccessedAtAndCreateBankActiveLog(true);

        // ログデータの一括保存
        $this->saveAllLog();
    }

    /**
     * APIレスポンスのusrParameterのデータを作成する。
     * 経緯：当初diamondの情報もusr_user_parametersにあったが、課金基盤の導入に伴い、管理テーブルが変わった。
     *
     * @return UsrParameterData
     */
    public function makeUsrParameterData(
        UsrUserParameterInterface|UsrUserParameterEntity $usrUserParameter
    ): UsrParameterData {
        /** @var AppCurrencyDelegator $appCurrencyDelegator */
        $appCurrencyDelegator = app()->make(AppCurrencyDelegator::class);
        $summary = $appCurrencyDelegator->getCurrencySummary($usrUserParameter->getUsrUserId());

        return new UsrParameterData(
            $usrUserParameter->getLevel(),
            $usrUserParameter->getExp(),
            $usrUserParameter->getCoin(),
            $usrUserParameter->getStamina(),
            $usrUserParameter->getStaminaUpdatedAt(),
            $summary->getFreeAmount(),
            $summary->getPaidAmountApple(),
            $summary->getPaidAmountGoogle(),
        );
    }

    /**
     * ミッションの進捗判定と更新を実行する
     */
    private function updateMissionProgresses(): void
    {
        // ユーザーの認証が通っている場合のみ、ミッション進捗更新を行う

        $user = $this->getRequestUser();
        if ($user === null) {
            return;
        }

        $usrUserId = $user->id;
        $now = CarbonImmutable::now();

        $missionDelegator = app()->make(MissionDelegator::class);
        /** @var MissionDelegator $missionDelegator */
        $missionDelegator->handleAllUpdateTriggeredMissions($usrUserId, $now);
    }

    /**
     * ログデータを一括保存する
     */
    private function saveAllLog(): void
    {
        try {
            // 動作テストとしてログ保存トランザクション外で行う
            // ログデータの一括保存
            /** @var LogModelManager $logModelManager */
            $logModelManager = app()->make(LogModelManager::class);
            $logModelManager->saveAll();
        } catch (\Throwable $e) {
            // ログ保存の例外はエラーログ出力のみとしておく
            Log::error('Exception occurred in saveAllLog: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * APIリクエストを実行した認証ユーザー情報を取得する
     * @return CurrentUser|null null: 認証ユーザーがいない
     */
    private function getRequestUser(): ?CurrentUser
    {
        $user = auth()->user();
        if ($user instanceof CurrentUser) {
            return $user;
        }

        return null;
    }

    /**
     * ユーザーの1時間ごとのアクセス日時の更新を行い
     * BankF001のためにアクティブログも作成する
     *
     * @param bool $isSave true: 更新後にDB即時保存も行う、false: モデルの値の更新のみ行う。DB即時保存はしない
     */
    private function updateHourlyAccessedAtAndCreateBankActiveLog(bool $isSave = false): void
    {
        $user = $this->getRequestUser();
        if ($user === null) {
            return;
        }

        $usrUserId = $user->id;
        $now = CarbonImmutable::now();

        /** @var UsrUserLoginRepository $usrUserLoginRepository */
        $usrUserLoginRepository = app()->make(UsrUserLoginRepository::class);

        $usrUserLogin = $usrUserLoginRepository->get($usrUserId);
        if (is_null($usrUserLogin) || !$usrUserLogin->checkHourlyAccessUpdate($now)) {
            return;
        }

        if ($isSave) {
            $usrUserLoginRepository->updateHourlyAccessedAtWithSave(
                $usrUserId,
                $now->toDateTimeString()
            );
        } else {
            $usrUserLoginRepository->updateHourlyAccessedAt(
                $usrUserId,
                $now->toDateTimeString()
            );
        }

        $logBankService = app()->make(LogBankService::class);
        $logBankService->createLogBankActive(
            $usrUserId,
            new CarbonImmutable($user->getGameStartAt())
        );
    }
}
