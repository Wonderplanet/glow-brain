<?php

namespace App\Filament\Traits;

use App\Constants\Database;
use App\Facades\Promotion;
use App\Jobs\IndividualMessageRegister;
use App\Models\Adm\AdmMessageDistributionIndividualInput;
use App\Models\Adm\AdmUser;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Models\Adm\Enums\AdmMessageTargetIdInputTypes;
use App\Models\Mng\MngMessage;
use App\Models\Mng\MngMessageI18n;
use App\Models\Mng\MngMessageReward;
use App\Models\Usr\UsrTemporaryIndividualMessage;
use App\Repositories\Adm\Base\AdmMessageDistributionInputRepositoryInterface;
use App\Traits\DatabaseTransactionTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait MessageEditTrait
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
        if ($this->isAlDistribution && Promotion::isPromotionDestinationEnvironment()) {
            // 全体配布且つ、昇格先環境(編集NG。配布実行のみ可能)
            return [
                $this->submitButton(),
                $this->backButton(),
            ];
        } else {
            // 昇格元環境(編集OK)
            return [
                $this->restoreButton(),
                $this->pendingButton(),
                $this->submitButton(),
                $this->updateButton(),
                $this->deleteButton(),
                $this->backButton(),
            ];
        }
    }

    /**
     * 下書きに戻すボタン
     *  create_statusを下書き(Editing)状態にする
     *  すでに下書き状態の場合はボタンを非活性にする
     *
     * @return Action
     */
    public function restoreButton(): Action
    {
        return Action::make('restoreButton')
            ->label('下書きに戻す')
            ->color('gray')
            ->requiresConfirmation()
            ->visible(function () {
                if ($this->createStatus === AdmMessageCreateStatuses::Editing) {
                    // すでに下書き状態の場合は非表示
                    return false;
                }
                if (
                    $this->createStatus === AdmMessageCreateStatuses::Approved
                    && $this->distributionStartDate < $this->nowJst
                ) {
                    // 配布済みの場合は、配布開始日時が過ぎていたら非表示
                    return false;
                }
                // 他の状態は全て表示
                return true;
            })
            ->action(fn() => $this->updateCreateStatusByEditing());
    }

    /**
     * 配布申請ボタン
     *  下書きデータの更新とcreate_statusを申請中(Pending)状態にする
     *  下書き中の時のみ表示する
     *
     * @return Action
     */
    public function pendingButton(): Action
    {
        return Action::make('pendingButton')
            ->label('配布申請')
            ->requiresConfirmation()
            ->visible(fn() => $this->createStatus === AdmMessageCreateStatuses::Editing)
            ->action(fn() => $this->update(AdmMessageCreateStatuses::Pending, false));
    }

    /**
     * 配布実行ボタン
     *  opprデータ登録を実行
     *   個別配布の場合は個別配布キュー登録も実行する
     *   申請中の時のみ表示する
     *
     * @return Action
     */
    public function submitButton(): Action
    {
        return Action::make('submitButton')
            ->label('配布実行')
            ->visible(fn() => $this->createStatus === AdmMessageCreateStatuses::Pending)
            ->requiresConfirmation()
            ->action(fn() => $this->register());
    }

    /**
     * 上書き保存ボタン
     *  入力された内容をもとに下書きデータを更新する
     *  create_statusが作成中のみ表示される
     *
     * @return Action
     */
    public function updateButton(): Action
    {
        return Action::make('updateButton')
            ->label('上書き保存')
            ->color('info')
            ->visible(fn() => $this->createStatus === AdmMessageCreateStatuses::Editing)
            ->requiresConfirmation()
            ->action(fn() => $this->update(AdmMessageCreateStatuses::Editing, true));
    }

    /**
     * 削除ボタン
     *  下書きデータを削除
     *  create_statusが作成中の時のみ表示される
     *  mngデータが登録済みだと、作成中になっても表示されない
     *
     * @return Action
     */
    public function deleteButton(): Action
    {
        return Action::make('deleteButton')
            ->label('削除')
            ->color('danger')
            ->visible(fn() => $this->createStatus === AdmMessageCreateStatuses::Editing && !$this->isRegisteredMngMessage)
            ->requiresConfirmation()
            ->action(fn() => $this->delete());
    }

    /**
     * 一覧に戻るボタン
     *
     * @return Action
     */
    public function backButton(): Action
    {
        return Action::make('backButton')
            ->label('戻る')
            ->color('gray')
            ->url($this->getRedirectUrl());
    }

    /**
     * 画面初回遷移時に起動
     */
    public function traitMount(int|string $record)
    {
        // urlの 'messages/{record}/edit' からrecord(id)を取得しセット
        $this->admMessageDistributionInputId = $record;

        $this->createStatus = AdmMessageCreateStatuses::Approved;
        $this->nowJst = CarbonImmutable::now();

        // DBから下書きデータを取得して各フォームに設定する
        /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
        $admMessageDistributionInputRepository = app()->make($this->repository);
        $admMessageDistributionInput = $admMessageDistributionInputRepository->getById($this->admMessageDistributionInputId);

        $this->createStatus = $admMessageDistributionInput->getCreateStatus();
        $this->isInputtable = $admMessageDistributionInput->getCreateStatus() === AdmMessageCreateStatuses::Editing; // 下書き状態ならform入力可能、それ以外は不可
        $this->isRegisteredMngMessage = !is_null($admMessageDistributionInput->getMngMessageId()); // mngMessageIdがnullならfalse(未登録),nullじゃなければtrue(登録済み)

        $mngMessage = $admMessageDistributionInput->getUnserializedMngMessages();
        /** @var Collection $mngMessageDistributionCollection */
        $mngMessageDistributionCollection = $admMessageDistributionInput->getUnserializedMngMessageDistributions();
        /** @var Collection $mngMessageI18nCollection */
        $mngMessageI18nCollection = $admMessageDistributionInput->getUnserializedMngMessageI18ns();
        $i18nJa = $mngMessageI18nCollection->first(fn($row) => $row['language'] === 'ja');
        $i18nEn = $mngMessageI18nCollection->first(fn($row) => $row['language'] === 'en');
        $i18nHant = $mngMessageI18nCollection->first(fn($row) => $row['language'] === 'zh-Hant');

        $isAlDistribution = $admMessageDistributionInput->getTargetType() === 'All';
        $checkAddExpiredDays = match ($mngMessage['add_expired_days']) {
            7 => 'sevenDays',
            30 => 'thirtyDays',
            default => 'otherDays',
        };

        /** @var CarbonImmutable|string $accountCreatedStartAt */
        $accountCreatedStartAt = $mngMessage['account_created_start_at']
            ? $mngMessage['account_created_start_at']->format('Y-m-d H:i:s')
            : '';
        /** @var CarbonImmutable|string $accountCreatedStartAt */
        $accountCreatedEndAt = $mngMessage['account_created_end_at']
            ? $mngMessage['account_created_end_at']->format('Y-m-d H:i:s')
            : '';

        $targetIdsTxt = $admMessageDistributionInput->getTargetIdsTxt();
        $playerId = '';
        if (!is_null($targetIdsTxt) && $admMessageDistributionInput->getDisplayTargetIdInputType() === AdmMessageTargetIdInputTypes::Input->value) {
            // 個別入力の場合はアンシリアライズして対象IDを取得
            //  CSVだと大量人数アンシリアライズした場合画面が重たくなるため、ここでは個別入力のみにしている
            $targetIds = unserialize($targetIdsTxt, ['allowed_classes' => false]);
            $playerId = reset($targetIds); // 配列の先頭のデータを取得
        }

        $itemSelected = $mngMessageDistributionCollection
            ->map(function ($row) {
                $distributionId = $row['resource_id'] ?? 0;
                return [
                    'distributionType' => $row['resource_type'],
                    'distributionId' => $distributionId,
                    'distributionQuantity' => $row['resource_amount'],
                ];
            })->toArray();

        // 初期表示データとしてセット
        $this->titleJp = $i18nJa['title'];
        $this->isAlDistribution = $isAlDistribution;
        $this->checkCreatedAccount = $admMessageDistributionInput->getAccountCreatedType();
        $this->checkPlayerType = $isAlDistribution ? '' : $admMessageDistributionInput->getTargetType();
        $this->playerId = $playerId;
        $this->targetIdCollection = collect();
        $this->targetIdsTxt = $targetIdsTxt;
        $this->targetIdInputType = $admMessageDistributionInput->getDisplayTargetIdInputType();
        $this->accountCreatedStartAt = $accountCreatedStartAt;
        $this->accountCreatedEndAt = $accountCreatedEndAt;
        $this->distributionStartDate = $mngMessage['start_at']->format('Y-m-d H:i:s');
        $this->distributionEndDate = $mngMessage['expired_at']->format('Y-m-d H:i:s');
        $this->checkAddExpiredDays = $checkAddExpiredDays;
        $this->inputAddExpiredDays = $checkAddExpiredDays === 'otherDays' ? $mngMessage['add_expired_days'] : '';
        $this->bodyJp = $i18nJa['body'];
        $this->itemSelected = $itemSelected;
        $this->isRegisteredIndividual = UsrTemporaryIndividualMessage::query()
            ->where('mng_message_id', $admMessageDistributionInput->getMngMessageId())
            ->exists();
        $this->admPromotionTagId = $admMessageDistributionInput->getAdmPromotionTagId();
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
     * create_statusを下書き状態に更新する
     *
     * @return void
     * @throws \Throwable
     */
    public function updateCreateStatusByEditing(): void
    {
        try {
            $this->transaction(
                function () {
                    /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
                    $admMessageDistributionInputRepository = app()->make($this->repository);
                    $admMessageDistributionInputRepository->update(
                        ['create_status' => AdmMessageCreateStatuses::Editing],
                        $this->admMessageDistributionInputId
                    );
                },
                [Database::TIDB_CONNECTION]
            );

            // 状態更新の通知表示
            Notification::make()
                ->title('状態を作成中に更新しました')
                ->color('success')
                ->send();

            $this->redirect($this->getResource()::getUrl('edit', [
                'record' => $this->admMessageDistributionInputId,
            ]));
        } catch (\Exception $e) {
            Log::error('EditMessage updateCreateStatus Error', [$e]);
        }
    }

    /**
     * 更新用の対象IDセットメソッド
     *
     * @return void
     */
    public function setTargetIdCollection(): void
    {
        if ($this->isAlDistribution) {
            // 全体配布であれば何もしない
            return;
        }

        if (!is_null($this->targetIdsTxt)) {
            // シリアライズしたtargetIdが存在するならアンシリアライズしてセットする
            $targetIds = unserialize($this->targetIdsTxt, ['allowed_classes' => false]);
            $this->targetIdCollection = collect($targetIds);

            // フォームに入力された内容は処理しないようにここで終了
            return;
        }

        // フォームに新たに入力＆アップロードされた情報をセット
        $this->setTargetIdCollectionFormInput();
    }

    /**
     * 下書きデータを更新する
     *
     * @param AdmMessageCreateStatuses $status
     * @param boolean $execByUpdateButton
     * @return void
     */
    public function update(AdmMessageCreateStatuses $status, bool $execByUpdateButton): void
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
                function () use ($status) {
                    // 入力値を元に保存するパラメータを取得
                    [
                        $startAt,
                        $expiredAt,
                        $targetType,
                        $targetIdsTxt,
                        $targetIdInputType,
                        $serializeMngMessage,
                        $serializeMngDistributions,
                        $serializeMngI18ns
                    ] = $this->formattedForParams();

                    /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
                    $admMessageDistributionInputRepository = app()->make($this->repository);
                    $admMessageDistributionInputRepository->update(
                        [
                            'create_status' => $status,
                            'title' => $this->titleJp,
                            'start_at' => $startAt,
                            'expired_at' => $expiredAt,
                            'mng_messages_txt' => $serializeMngMessage,
                            'mng_message_distributions_txt' => $serializeMngDistributions,
                            'mng_message_i18ns_txt' => $serializeMngI18ns,
                            'target_type' => $targetType,
                            'target_ids_txt' => $targetIdsTxt,
                            'display_target_id_input_type' => $targetIdInputType,
                            'account_created_type' => $this->checkCreatedAccount,
                            'adm_promotion_tag_id' => $this->admPromotionTagId,
                        ],
                        $this->admMessageDistributionInputId
                    );
                },
                [Database::TIDB_CONNECTION]
            );

            // 不要になった変数のうちデータ量の多いものを削除
            //  対象人数が多いとリダイレクト処理がうまくいかないことがあるのでここで削除している
            unset($this->targetIdCollection);

            // 更新が完了したら一時ファイルを削除する
            $this->deleteTmpFiles();

            if ($execByUpdateButton) {
                // 下書き保存ボタン実行の通知表示
                Notification::make()
                    ->title('下書きデータを更新しました')
                    ->color('success')
                    ->send();
            } else {
                Notification::make()
                    ->title('状態を申請中に更新しました')
                    ->color('success')
                    ->send();
                // 申請中に更新後は一覧画面に遷移
                $this->redirect($this->getRedirectUrl());
            }
        } catch (\Exception $e) {
            Log::error('EditMessage update Error', [$e]);
        }
    }

    /**
     * 下書きデータをもとに登録処理を実行
     *
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        try {
            $this->transaction(
                function () {
                    /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
                    $admMessageDistributionInputRepository = app()->make($this->repository);

                    // 下書きテーブルからデータを取得
                    /** @var AdmMessageDistributionIndividualInput $admMessageDistributionInput */
                    $admMessageDistributionInput = $admMessageDistributionInputRepository->getById($this->admMessageDistributionInputId);

                    // unserializeしたMngデータを取得
                    $mngMessageData = $admMessageDistributionInput->getUnserializedMngMessages();
                    $distributionCollection = $admMessageDistributionInput->getUnserializedMngMessageDistributions();
                    $i18nCollection = $admMessageDistributionInput->getUnserializedMngMessageI18ns();

                    // Mngデータ登録実行
                    /** @var MngMessage $mngMessage */
                    $mngMessage = $this->mngRegister(
                        $mngMessageData,
                        $distributionCollection,
                        $i18nCollection,
                        $admMessageDistributionInput->getMngMessageId(),
                    );

                    // 下書きテーブルのcreate_statusを配布済み、登録したmng_meeage_idを更新
                    $admMessageDistributionInputRepository->update(
                        [
                            'create_status' => AdmMessageCreateStatuses::Approved,
                            'mng_message_id' => $mngMessage->id,
                        ],
                        $this->admMessageDistributionInputId
                    );

                    // 個別配布キュー登録
                    $this->dispatchIndividualRegister($this->admMessageDistributionInputId, $mngMessage->id);
                },
                [
                    Database::ADMIN_CONNECTION,
                    Database::MANAGE_DATA_CONNECTION,
                ]
            );

            // キャッシュを削除
            $this->deleteMngMessageCache();

            // 登録完了通知
            Notification::make()
                ->title('メッセージデータ登録が完了しました')
                ->color('success')
                ->send();

            // 登録完了後は一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Log::error('EditMessage register Error', [$e]);
        }
    }

    /**
     * mng関連のデータ登録
     *  登録済みの場合は更新する
     *
     * @param array $mngMessageData
     * @param Collection $mngMessageDistributions
     * @param Collection $mngMessageI18ns
     * @param ?string $mngMessageId
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function mngRegister(
        array $mngMessageData,
        Collection $mngMessageDistributions,
        Collection $mngMessageI18ns,
        ?string $mngMessageId
    ): \Illuminate\Database\Eloquent\Model {
        $mngMessage = null;
        $mngMessageCreateData = $mngMessageData;
        if ($mngMessageId !== null) {
            $mngMessage = MngMessage::query()
                ->where('id', $mngMessageId)
                ->first();
            $mngMessageCreateData['id'] = $mngMessageId;
        }

        if (is_null($mngMessage)) {
            // nullの場合は未登録のため新規作成
            /** @var MngMessage $mngMessage */
            $mngMessage = MngMessage::query()
                ->create($mngMessageCreateData);
            $mngMessageId = $mngMessage->id;
        } else {
            // 登録済みの場合はmng_messagesを更新
            MngMessage::query()
                ->where('id', $mngMessageId)
                ->update($mngMessageData);

            // mng_message_distoributionsとmng_message_i18nは一旦削除
            MngMessageReward::query()
                ->where('mng_message_id', $mngMessageId)
                ->delete();
            MngMessageI18n::query()
                ->where('mng_message_id', $mngMessageId)
                ->delete();
        }

        // mng_message_distoributionsを登録
        foreach ($mngMessageDistributions as $distribution) {
            $distribution['mng_message_id'] = $mngMessageId;
            MngMessageReward::query()->create(
                $distribution
            );
        }

        // mng_message_i18nを登録
        foreach ($mngMessageI18ns as $i18n) {
            $i18n['mng_message_id'] = $mngMessageId;
            MngMessageI18n::query()->create(
                $i18n
            );
        }

        return MngMessage::query()
            ->where('id', $mngMessageId)
            ->first();
    }

    /**
     * メッセージ個別配布キューを入れ込む
     *  全体配布、個別配布がすでに登録済みの場合はスキップ
     *
     * @param string $admMessageDistributionInputId
     * @param string $mngMessageId
     * @return void
     */
    private function dispatchIndividualRegister(string $admMessageDistributionInputId, string $mngMessageId): void
    {
        if ($this->isAlDistribution) {
            // 全体配布の場合は何もしない
            return;
        }

        if ($this->isRegisteredIndividual) {
            // 個別配布登録済みのmngMessageIの場合はスキップ
            Log::info('EditMessage dispatchIndividualRegister 登録済みのため個別配布キュー登録をスキップ', ['mngMessageId' => $mngMessageId]);
            return;
        }

        /** @var AdmUser $admUser */
        $admUser = auth()->user();
        IndividualMessageRegister::dispatch($admMessageDistributionInputId, $admUser?->id);

        // データベース通知送信
        /*
        $admUser->notifyNow(
            Notification::make()
                ->title('メッセージ個別配布処理を実行中です')
                ->info()
                ->toDatabase()
        );
        */
    }

    /**
     * 削除実行
     *
     * @return void
     */
    public function delete(): void
    {
        try {
            $this->transaction(
                function () {
                    /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
                    $admMessageDistributionInputRepository = app()->make($this->repository);
                    $admMessageDistributionInputRepository->delete(
                        $this->admMessageDistributionInputId
                    );

                    // 削除完了通知
                    Notification::make()
                        ->title("id:{$this->admMessageDistributionInputId}を削除しました")
                        ->color('success')
                        ->send();
                },
                [Database::TIDB_CONNECTION]
            );
            // 削除後は一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Log::error('EditMessage delete Error', [$e]);
        }
    }
}
