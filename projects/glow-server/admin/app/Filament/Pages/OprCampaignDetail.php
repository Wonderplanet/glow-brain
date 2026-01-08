<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\OprCampaignResource;
use App\Models\Opr\OprCampaign;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class OprCampaignDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.opr-campaign-detail';

    protected static ?string $title = 'キャンペーン詳細';

    public string $oprCampaignId = '';

    protected $queryString = [
        'oprCampaignId',
    ];

    protected function getResourceClass(): string
    {
        return OprCampaignResource::class;
    }

    protected function getMstModelByQuery(): ?OprCampaign
    {
        return OprCampaign::query()
            ->with('opr_campaign_i18n')
            ->where('id', $this->oprCampaignId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('opr_campaigns.id: %s', $this->oprCampaignId);
    }

    protected function getSubTitle(): string
    {
        $oprCampaign = $this->getMstModel();

        return StringUtil::makeIdNameViewString($oprCampaign->id, '');
    }

    public function table(Table $table): Table
    {
        $query = OprCampaign::query()
            ->with('opr_campaign_i18n');

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    private function infoList(): InfoList
    {
        $oprCampaign = $this->getMstModel();
        $oprCampaignI18n = $oprCampaign->opr_campaign_i18n;

        $effectValueSuffix = $oprCampaign->isChallengeCountCampaign() ? '回' : '%';
        $state = [
            'id' => $oprCampaign->id,
            'campaign_type' => $oprCampaign->getCampaignTypeLabelAttribute(),
            'target_type' => $oprCampaign->getCampaignTargetTypeLabelAttribute(),
            'difficulty' => $oprCampaign->getDifficultyLabelAttribute(),
            'target_id_type' => $oprCampaign->getTargetIdTypeLabelAttribute(),
            'effect_value' => $oprCampaign->effect_value . $effectValueSuffix,
            'asset_key' => $oprCampaign->asset_key,
            'term' => $oprCampaign->start_at . ' ~ ' . $oprCampaign->end_at,
            'description' => $oprCampaignI18n?->description ?? '',
        ];
        $fieldset = Fieldset::make('キャンペーン詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('description')->label('説明'),
                TextEntry::make('term')->label('有効期間'),
                TextEntry::make('campaign_type')->label('キャンペーン種別'),
                TextEntry::make('target_type')->label('キャンペーン対象種別'),
                TextEntry::make('difficulty')->label('キャンペーン対象難易度'),
                TextEntry::make('target_id_type')->label('キャンペーン対象IDタイプ'),
                TextEntry::make('effect_value')->label('効果値'),
                TextEntry::make('asset_key')->label('アセットキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }
}
