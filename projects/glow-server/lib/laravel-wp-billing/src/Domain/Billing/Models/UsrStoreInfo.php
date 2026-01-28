<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use WonderPlanet\Domain\Currency\Models\BaseUsrModel;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;

/**
 * ユーザーのショップ情報を管理するModel
 *
 * 月の購入金額や年齢などの情報を管理する
 *
 * $paidPriceはJPYの累計のみ想定しているためintとして扱われるが、スキーマ定義は他と型を合わせるためdecimal(20,6)となっている
 * Eloquentモデルはスキーマに合わせてstringになるが、その他の内部処理はintで扱う
 *
 * total_vip_pointはVIPポイントの累計を管理する
 *
 * @property string $id
 * @property string $usr_user_id
 * @property int $age
 * @property string $paid_price
 * @property int $total_vip_point
 * @property ?string $renotify_at 次回年齢確認日。確認する必要がない場合はnullになる
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class UsrStoreInfo extends BaseUsrModel
{
    use HasEntityTrait;
    use SoftDeletes;
}
