<?php

namespace App\Filament\Resources\UsrStoreAllowanceResource\Pages;

use App\Constants\Database;
use App\Filament\Resources\UsrStoreAllowanceResource;
use App\Models\Usr\UsrStoreAllowance;
use App\Traits\DatabaseTransactionTrait;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;

class CreateUsrStoreAllowance extends CreateRecord
{
    use DatabaseTransactionTrait;

    protected static string $resource = UsrStoreAllowanceResource::class;

    protected string $modalContentView = 'filament.common.confirm-modal-content';

    protected static bool $canCreateAnother = false; // 「続けて作成ボタン」を非表示

    /**
     * CreateRecord->getCreateFormActionのオーバーライド
     * ボタン実行時に確認モーダルを表示し、入力内容を確認できるようにカスタマイズ
     *
     * @return Action
     */
    protected function getCreateFormAction(): Action
    {
        return
            Action::make('create')
            ->label('作成')
            ->requiresConfirmation()
            ->modalContent(view($this->modalContentView, ['fields' => UsrStoreAllowance::KEY_NAMES, 'inputs' => $this->data, 'requiredColumns' => ['device_id']]))
            ->action(fn () => $this->createRecord())
            ->disabled(function ()  {
                // 入力値がnull、空文字の場合は作成ボタンをおさせない
                $found = collect(UsrStoreAllowance::REQUIRED_COLUMNS)
                    ->first(fn($column) => empty($this->data[$column]));
                return !is_null($found);
            });
    }

    /**
     * レコード生成
     *
     * @return void
     * @throws \Throwable
     */
    private function createRecord(): void
    {
        try {
            $this->transaction(
                function () {
                    /** @var BillingAdminDelegator $billingAdminDelegator */
                    $billingAdminDelegator = app()->make(BillingAdminDelegator::class);

                    $data = $this->data;
                    $billingAdminDelegator->insertAllowanceAndLog(
                        $data['usr_user_id'],
                        $data['os_platform'],
                        $data['billing_platform'],
                        $data['product_id'],
                        $data['mst_store_product_id'],
                        $data['product_sub_id'],
                        $data['device_id'],
                        'admin cs tool add user store allowance'
                    );
                },
                [
                    Database::TIDB_CONNECTION
                ]
            );

            // 登録完了後は一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        } catch (QueryException $qe) {
            $noticeTitle = 'エラーが発生しました。<br/>管理者にお問い合わせください。';
            if ($qe->getCode() === '23000') {
                // user_id_platform_product_id_uniqueの重複エラーを検知
                $noticeTitle = '入力されたユーザーID,課金プラットフォーム,プロダクトIDの組み合わせはすでに登録済みです。';
            }

            Log::error('', [$qe]);
            Notification::make()
                ->title($noticeTitle)
                ->color('danger')
                ->send();
        } catch (\Exception $e) {
            Log::error('', [$e]);
            Notification::make()
                ->title('エラーが発生しました。<br/>管理者にお問い合わせください。')
                ->color('danger')
                ->send();
        }
    }
}
