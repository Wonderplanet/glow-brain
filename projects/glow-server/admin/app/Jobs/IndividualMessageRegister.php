<?php

namespace App\Jobs;

use App\Constants\Database;
use App\Constants\SystemConstants;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Models\Adm\Enums\AdmMessageTargetTypes;
use App\Models\Usr\UsrTemporaryIndividualMessage;
use App\Models\Usr\UsrUserProfile;
use App\Repositories\Adm\AdmMessageDistributionIndividualInputRepository;
use App\Repositories\Adm\AdmMessageDistributionInputRepository;
use App\Traits\DatabaseTransactionTrait;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

/**
 * 個別配布メッセージ送信を実行するキュージョブクラス
 */
class IndividualMessageRegister extends BaseJob
{
    use DatabaseTransactionTrait;

    // 一括insertの1クエリの登録件数
    private int $bulkInsertChunk = 5000;

    // usr_user_id取得クエリの1回の取得件数
    //  placeholderの指定は65535までの為、それ以上のidを指定するとエラーになってしまう
    //  エラーを回避する為、取得クエリも分割実行するようにした
    private int $getUserIdChunk = 50000;

    private int $admMessageDistributionIndividualInputId;

    private const CLASS_NAME = 'IndividualMessageRegister';
    private const FAILED_ERROR_MSG = '個別配布メッセージ ユーザーデータ登録でエラーが発生しました。<br />'
        . '再実行するかサーバー管理者にお問い合わせ下さい。';

    /**
     * Create a new job instance.
     */
    public function __construct(int $admMessageDistributionIndividualInputId, ?string $admUserId = null)
    {
        $this->admMessageDistributionIndividualInputId = $admMessageDistributionIndividualInputId;
        $this->admUserId = $admUserId;
        $this->className = self::CLASS_NAME;
        $this->failedErrorMsg = self::FAILED_ERROR_MSG;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', SystemConstants::MAX_MEMORY_LIMIT);

        Log::info("[queue] IndividualMessageRegister START");

        $this->transaction(
            function () {
                // admテーブルから対象データを取得
                /** @var AdmMessageDistributionIndividualInputRepository $admMessageDistributionIndividualRepository */
                $admMessageDistributionIndividualRepository =
                    app()->make(AdmMessageDistributionIndividualInputRepository::class);
                $admMessageDistributionIndividualInput =
                    $admMessageDistributionIndividualRepository->getById($this->admMessageDistributionIndividualInputId);
                if (is_null($admMessageDistributionIndividualInput)) {
                    throw new \RuntimeException("admMessageDistributionInput not found Id:{$this->admMessageDistributionIndividualInputId}");
                }
                if ($admMessageDistributionIndividualInput->getCreateStatus() !== AdmMessageCreateStatuses::Approved) {
                    throw new \RuntimeException(
                        "create_status not Approved Id:{$this->admMessageDistributionIndividualInputId}"
                        . " create_status:{$admMessageDistributionIndividualInput->getCreateStatus()->value}"
                    );
                }
                if (is_null($admMessageDistributionIndividualInput->target_ids_txt)) {
                    throw new \RuntimeException(
                        "target_ids_txt is null Id:{$this->admMessageDistributionIndividualInputId}"
                    );
                }

                // 対象ユーザーを取得
                $targetIds = unserialize($admMessageDistributionIndividualInput->target_ids_txt, ['allowed_classes' => false]);
                $usrUserIds = $targetIds;
                // 対象idがMyIdの場合はusrUserIdを取得する
                if ($admMessageDistributionIndividualInput->target_type === AdmMessageTargetTypes::MyId->value) {
                    unset($usrUserIds);
                    $chunkTargetIds = array_chunk($targetIds, $this->getUserIdChunk);
                    $tmpUserIdCollection = collect();
                    foreach ($chunkTargetIds as $ids) {
                        $tmpUserIds = UsrUserProfile::query()
                            ->whereIn('my_id', $ids)
                            ->get('usr_user_id')
                            ->pluck('usr_user_id')
                            ->toArray();
                        $tmpUserIdCollection->push($tmpUserIds);
                    }
                    $usrUserIds = $tmpUserIdCollection->flatten(2)->toArray();
                    unset($tmpUserIdCollection);
                }

                // 負荷対策のため分割実行する
                $chunkUsrUserIds = array_chunk($usrUserIds, $this->bulkInsertChunk);
                foreach ($chunkUsrUserIds as $index => $ids) {
                    $result = $this->bulkInsertUsrTemporaryIndividualMessage(
                        collect($ids),
                        $admMessageDistributionIndividualInput->mng_message_id
                    );

                    if (!$result) {
                        $chunkMax = count($chunkUsrUserIds);
                        throw new \Exception("failed register UsrTemporaryIndividualMessage {$index} / {$chunkMax}");
                    }
                }
            },
            [Database::TIDB_CONNECTION]
        );

        Log::info("[queue]{$this->className} END");

        // 管理者ユーザーへの通知実行
        $this->notification('success', "個別配布メッセージ ユーザーデータ登録 が完了しました id:{$this->admMessageDistributionIndividualInputId}");
    }

    /**
     * 個別配布メッセージ一時保存テーブルへの一括登録
     *
     * @param Collection $usrUserIdCollection
     * @param string $mngMessageId
     * @return bool
     */
    private function bulkInsertUsrTemporaryIndividualMessage(Collection $usrUserIdCollection, string $mngMessageId): bool
    {
        $now = CarbonImmutable::now();

        // 一括登録用にデータを生成
        $inputs = $usrUserIdCollection->map(fn($row) => [
            'id' => (string) Uuid::uuid4(),
            'usr_user_id' => $row,
            'mng_message_id' => $mngMessageId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        return UsrTemporaryIndividualMessage::query()
            ->insert($inputs);
    }
}
