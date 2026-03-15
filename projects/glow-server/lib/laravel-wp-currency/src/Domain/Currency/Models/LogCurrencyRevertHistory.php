<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 一次通貨の返却を行なった際のログ記録用クラス
 *
 * @property string $id
 * @property string $usr_user_id
 * @property string $comment
 *
 * これらの情報でひとつの返却単位としている
 * @property string $log_trigger_type
 * @property string $log_trigger_id
 * @property string $log_trigger_name
 * @property string $log_trigger_detail
 * @property string $log_request_id_type
 * @property string $log_request_id
 * @property \Illuminate\Support\Carbon $log_created_at
 *
 * 打ち消しを行う対象の内容を記録するため、マイナス値が記録されている。
 * 例: 100消費した状態で10返却した場合、-100のうち-10を取り消したと解釈するので-10が入っている
 * 　　もともとはログにあったhange_paid_amountやchange_free_amountをそのまま入れていたが、
 *    途中で一部返却を行うことになったため、上記のような解釈となった
 * @property int $log_change_paid_amount
 * @property int $log_change_free_amount
 *
 * @property string $trigger_type
 * @property string $trigger_id
 * @property string $trigger_name
 * @property string $trigger_detail
 * @property string $request_id_type
 * @property string $request_id
 * @property string $nginx_request_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LogCurrencyRevertHistory extends BaseLogModel
{
}
