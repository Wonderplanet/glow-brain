<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Filament\Tables\Filters;

use Closure;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

/**
 * テキスト検索フィルタを用意するクラス
 *
 * 対象のカラムにlikeで検索するだけの簡易機能
 * これ以上の機能が必要な場合は、別途queryで定義すること
 */
class TextFilter extends BaseColumnFilter
{
    /**
     * formに設定されるコンポーネント
     *
     * @var TextInput
     */
    private TextInput $compornent;

    /**
     * 初期設定
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->compornent = TextInput::make('searchtext');
        $this->form([
            $this->compornent,
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query->where($this->columnName, 'like', "%{$data['searchtext']}%");
        })
        ->indicateUsing(function (array $data): ?string {
            return $data['searchtext'] ? $this->label . ' ' . $data['searchtext'] : null;
        });
    }

    /**
     * フィルタの検索カラムに表示するテキストを設定
     *
     * formを使うと元のlabelでは反映されないので、ここでフックする
     *
     * @param string | Closure | null $label
     * @return static
     */
    public function label(string | Closure | null $label): static
    {
        parent::label($label);
        $this->compornent->label($label);
        return $this;
    }
}
