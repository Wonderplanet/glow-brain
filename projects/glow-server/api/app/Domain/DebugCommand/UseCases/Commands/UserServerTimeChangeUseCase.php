<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Debug\Entities\DebugUserTimeSetting;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;
use Carbon\CarbonImmutable;

/**
 * ユーザーサーバー時間変更コマンド
 */
class UserServerTimeChangeUseCase extends BaseParameterizedCommands
{
    public function __construct(
        private DebugUserTimeSettingRepository $debugUserTimeSettingRepository,
    ) {
    }

    private const YEAR_MIN = 1970;
    private const YEAR_MAX = 2037;

    private const MONTH_MIN = 1;
    private const MONTH_MAX = 12;

    private const DAY_MIN = 1;
    private const DAY_MAX = 31;

    private const HOUR_MIN = 0;
    private const HOUR_MAX = 23;

    private const MINUTE_MIN = 0;
    private const MINUTE_MAX = 59;

    protected string $name = 'ユーザーサーバー時間変更';
    protected string $description = 'ユーザーのサーバー時間を指定した日時に変更します';

    protected array $requiredParameters = [
        'year' => [
            'type' => 'integer',
            'min' => self::YEAR_MIN,
            'max' => self::YEAR_MAX,
            'description' => '西暦',
        ],
        'month' => [
            'type' => 'integer',
            'min' => self::MONTH_MIN,
            'max' => self::MONTH_MAX,
            'description' => '月',
        ],
        'day' => [
            'type' => 'integer',
            'min' => self::DAY_MIN,
            'max' => self::DAY_MAX,
            'description' => '日',
        ],
        'hour' => [
            'type' => 'integer',
            'min' => self::HOUR_MIN,
            'max' => self::HOUR_MAX,
            'description' => '時間',
        ],
        'minute' => [
            'type' => 'integer',
            'min' => self::MINUTE_MIN,
            'max' => self::MINUTE_MAX,
            'description' => '分',
        ],
    ];

    /**
     * パラメータ付きコマンドの実行
     * @param CurrentUser $user
     * @param int $platform
     * @param array<string, mixed> $params
     */
    protected function doExecWithParams(CurrentUser $user, int $platform, array $params): void
    {
        $targetDateTime = CarbonImmutable::create(
            $params['year'],
            $params['month'],
            $params['day'],
            $params['hour'],
            $params['minute'],
            0,
            'Asia/Tokyo',
        );

        $realNowJst = CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo');
        $debugUserTimeSetting = new DebugUserTimeSetting($targetDateTime, $realNowJst);
        $this->debugUserTimeSettingRepository->put($user->getId(), $debugUserTimeSetting);
    }
}
