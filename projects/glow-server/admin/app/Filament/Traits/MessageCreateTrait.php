<?php

namespace App\Filament\Traits;

use App\Constants\Database;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Repositories\Adm\Base\AdmMessageDistributionInputRepositoryInterface;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Support\Facades\Log;

/**
 * メッセージ配布データ新規作成
 */
trait MessageCreateTrait
{
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;
    use MessageFormTrait;

    public string $repository;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * 検索用フォームで使用するアクションボタンを返す
     *
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            $this->draftButton(),
            $this->pendingButton(),
            $this->cancelButton(),
        ];
    }

    /**
     * 下書き保存ボタン
     *
     * @return Action
     */
    public function draftButton(): Action
    {
        return Action::make('draftRegisterButton')
            ->label('下書き保存')
            ->requiresConfirmation()
            ->action(function (Action $action) {
                try {
                    $this->draftDataRegister(AdmMessageCreateStatuses::Editing);
                } catch (\Exception $e) {
                    $this->setErrorBag($e->validator->errors());
                    $action->cancel();
                }
            });
    }

    /**
     * 配布申請ボタン
     *
     * @return Action
     */
    public function pendingButton(): Action
    {
        return Action::make('pendingButton')
            ->label('配布申請')
            ->requiresConfirmation()
            ->action(function (Action $action) {
                try {
                    $this->draftDataRegister(AdmMessageCreateStatuses::Pending);
                } catch (\Exception $e) {
                    $this->setErrorBag($e->validator->errors());
                    $action->cancel();
                }
            });
    }

    /**
     * キャンセルボタン
     * 一覧に戻す
     *
     * @return Action
     */
    public function cancelButton(): Action
    {
        return Action::make('cancelButton')
            ->label('キャンセル')
            ->color('gray')
            ->url($this->getRedirectUrl());
    }

    /**
     * 画面初回遷移時に起動
     *
     * @return void
     */
    public function traitMount(): void
    {
        // 初期化
        $this->targetIdCollection = collect();
        $this->isInputtable = true;
        $this->createMount();
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $this->baseForm($form);
    }

    /**
     * 新規作成用の対象IDセットメソッド
     *
     * @return void
     */
    public function setTargetIdCollection(): void
    {
        if ($this->isAlDistribution) {
            // 全体配布であれば何もしない
            return;
        }

        // フォームに入力＆アップロードされた情報をセット
        $this->setTargetIdCollectionFormInput();
    }

    /**
     * 入力した内容を下書き状態で保存する
     *
     * @param AdmMessageCreateStatuses $createStatus
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function draftDataRegister(AdmMessageCreateStatuses $createStatus): void
    {
        // 対象人数が大量だった場合を考慮してメモリとタイムアウト設定を拡張
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300); // 5分

        // 個別配布ユーザーをセット
        $this->setTargetIdCollection();

        // バリデーション
        $this->customValidate();

        try {
            $this->transaction(
                function () use ($createStatus) {
                    // 入力値を元に保存するパラメータを取得
                    [
                        $startAt,
                        $expiredAt,
                        $targetType,
                        $targetIdsTxt,
                        $targetIdInputType,
                        $serializeMngMessage,
                        $serializeOprDistributions,
                        $serializeOprI18ns
                    ] = $this->formattedForParams();

                    // 下書き情報を保存
                    /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
                    $admMessageDistributionInputRepository = app()->make($this->repository);
                    $admMessageDistributionInputRepository->create(
                        $createStatus,
                        $this->titleJp,
                        $startAt,
                        $expiredAt,
                        null, // mng_messagesは生成されてないのでnullで固定
                        $serializeMngMessage,
                        $serializeOprDistributions,
                        $serializeOprI18ns,
                        $targetType,
                        $targetIdsTxt,
                        $targetIdInputType,
                        $this->checkCreatedAccount,
                        $this->admPromotionTagId,
                    );
                },
                [Database::TIDB_CONNECTION]
            );
            // 不要になった変数のうちデータ量の多いものを削除
            //  対象人数が多いとリダイレクト処理がうまくいかないことがあるのでここで削除している
            unset($this->targetIdCollection);

            // 登録完了通知
            Notification::make()
                ->title('メッセージデータ新規登録が完了しました')
                ->color('success')
                ->send();

            // 登録が完了したら一時ファイルを削除する
            $this->deleteTmpFiles();

            // 登録完了後は一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();
            Log::error('', [$e]);
        }
    }
}
