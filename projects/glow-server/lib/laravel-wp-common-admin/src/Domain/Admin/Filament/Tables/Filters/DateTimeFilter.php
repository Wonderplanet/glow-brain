<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Filament\Tables\Filters;

use Closure;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * 日時を指定するフィルタ
 * 入力された日時はJSTと解釈されて、DB側のUTCに変換されて検索される
 *
 * DateTimeFilter::make($name)で指定されたカラム名を検索する
 */
class DateTimeFilter extends BaseColumnFilter
{
    /**
     * 開始日時を設定するコンポーネント
     *
     * @var DateTimePicker
     */
    private DateTimePicker $from;

    /**
     * 終了日時を設定するコンポーネント
     *
     * @var DateTimePicker
     */
    private DateTimePicker $to;

    /**
     * 日時用のフィルタを設定
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->from = DateTimePicker::make('from');
        $this->to = DateTimePicker::make('to');
        $this->label($this->columnName);

        // 開始と終了日時を入力するフィルタを設定
        $this->form([
            $this->from,
            $this->to,
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query
                ->when(
                    $data['from'],
                    function (Builder $query, $date): Builder {
                        $carbon = new Carbon($date, config('wp_common_admin.form_input_time_zone'));
                        return $query->where($this->columnName, '>=', $carbon->setTimezone(config('wp_common_admin.form_input_time_zone')));
                    },
                )
                ->when(
                    $data['to'],
                    function (Builder $query, $date): Builder {
                        $carbon = new Carbon($date, config('wp_common_admin.form_input_time_zone'));
                        return $query->where($this->columnName, '<=', $carbon->setTimezone(config('wp_common_admin.form_input_time_zone')));
                    },
                );
        })
        ->indicateUsing(function (array $data): ?string {
            // fromとtoの両方とも入ってなかったらnull
            if (! ($data['from'] ?? false) && ! ($data['to'] ?? false)) {
                return null;
            }

            $indicates = [$this->label];
            // fromが入っていたら格納
            if ($data['from'] ?? false) {
                $indicates[] = $data['from'];
            }
            $indicates[] = '〜';
            // toが入っていたら格納
            if ($data['to'] ?? false) {
                $indicates[] = $data['to'];
            }

            return implode(' ', $indicates);
        });
    }

    /**
     * fromとtoの表示ラベルを設定する
     *
     * @param string | Closure | null $label
     * @return static
     */
    public function label(string | Closure | null $label): static
    {
        parent::label($label);

        // nullまたはclosureの場合はそのまま渡す
        if (is_null($label) || $label instanceof Closure) {
            $this->from->label($label);
            $this->to->label($label);
            return $this;
        }

        // そうでない場合はラベルを設定
        $this->from->label($label . ' 開始');
        $this->to->label($label . ' 終了');
        return $this;
    }
}
