<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Adm\AdmInformation;
use Carbon\CarbonImmutable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use App\Entities\Clock;

class BnUserSearchInfo extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;


    public function render()
    {
        return view('livewire.bn-user-search-info');
    }

    public function table(Table $table): Table
    {
        /** @var Clock $clock */
        $clock = app(Clock::class);
        $now = $clock->now();

        $query = AdmInformation::query()
            ->whereIn('enable', [0,1]);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('pre_notice_start_at')->label('日時'),
                TextColumn::make('title')->label('お知らせ件名'),
                TextColumn::make('enable')
                    ->label('ステータス')
                    ->searchable()
                    ->getStateUsing(
                        function ($record) {
                            return ($record->enable === 1)? '公開中' : '非公開';
                        }
                    ),
                TextColumn::make('content')->label('内容')
                    ->getStateUsing(
                        function ($record) {
                            $data = json_decode($record->html_json, true);
                            $text = $data['content'][0]['content'][0]['text'];
                            return $text;
                        }
                    ),
            ])
            ->paginated()
            ->defaultPaginationPageOption(5);
    }

    public function infoTable(): array
    {
        /** @var Clock $clock */
        $clock = app(Clock::class);
        $now = $clock->now();

        $admInformations = AdmInformation::query()
            ->where('post_notice_end_at', '>', $now)
            ->get()
            ->sortByDesc('pre_notice_start_at');

        $infoData = [];
        foreach ($admInformations as $admInformation) {
            $infoData[] = [
                'pre_notice_start_at' => CarbonImmutable::parse($admInformation->pre_notice_start_at)->toDateTimeString(),
                'title' => $admInformation->title,
                'enable' => $admInformation->isEnable() ? '公開中' : '非公開',
                'content' => mb_substr($admInformation->getPlainText(), 0, 100) . '...', // 100文字まで表示
            ];

        }

        return $infoData;
    }

}
