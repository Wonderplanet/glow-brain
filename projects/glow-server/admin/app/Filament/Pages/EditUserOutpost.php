<?php

namespace App\Filament\Pages;

use App\Constants\OutpostEnhancementType;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstOutpostEnhancement;
use App\Models\Usr\UsrArtwork;
use App\Models\Usr\UsrOutpost;
use App\Models\Usr\UsrOutpostEnhancement;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserOutpost extends UserDataBasePage
{
    use Authorizable;
    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::OUTPOST->value;

    public string $mstOutpostId = '';

    public string $mstArtworkId = '';
    public int $leaderPointSpeed = 1;
    public int $leaderPointLimit = 1;
    public int $outpostHp = 1;
    public int $summonInterval = 1;
    public int $leaderPointUp = 1;
    public int $rushChargeSpeed = 1;

    protected $queryString = [
        'userId',
        'mstOutpostId',
    ];

    public function mount()
    {
        parent::mount();

        // 設定中の原画ID取得
        $values = [];
        $values['mstArtworkId'] = UsrOutpost::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->get()
            ->first()
            ->mst_artwork_id;

        // 各強化要素の現在レベル取得
        $usrOutpostEnhancements = UsrOutpostEnhancement::query()
            ->with(['mst_outpost_enhancement'])
            ->where('usr_user_id', $this->userId)
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->get();
        foreach ($usrOutpostEnhancements as $usrOutpostEnhancement) {
            $outpostEnhancementType = $usrOutpostEnhancement->mst_outpost_enhancement->outpost_enhancement_type;
            $property = $this->convertOutpostEnhancementTypeToProperty($outpostEnhancementType);
            $values[$property] = $usrOutpostEnhancement->level;
        }

        $this->form->fill($values);
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserOutpost::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstOutpostId' => $this->mstOutpostId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        // 所持済み原画取得
        $components = [];
        $usrArtworks = UsrArtwork::query()
            ->with([
                'mst_artwork',
                'mst_artwork.mst_artwork_i18n'
            ])
            ->where('usr_user_id', $this->userId)
            ->get()
            ->mapWithKeys(function (UsrArtwork $record) {
                return [
                    $record->mst_artwork_id => sprintf(
                        '[%s] %s',
                        $record->mst_artwork_id,
                        $record->mst_artwork?->mst_artwork_i18n?->name
                    )
                ];
            })
            ->toArray();
        $components[] = Select::make('mstArtworkId')->label('原画情報')->options($usrArtworks)->searchable();

        // 各強化項目の最大レベル取得
        $mstOutpostEnhancements = MstOutpostEnhancement::query()
            ->with([
                'mst_outpost_enhancement_level' => function ($query) {
                    $query
                        ->select('mst_outpost_enhancement_id')
                        ->selectRaw('MAX(level) AS max_level')
                        ->groupBy(['mst_outpost_enhancement_id']);
                },
            ])
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->get();
        foreach ($mstOutpostEnhancements as $mstOutpostEnhancement) {
            $enhancementType = $mstOutpostEnhancement->outpost_enhancement_type;
            $property = $this->convertOutpostEnhancementTypeToProperty($enhancementType);
            if (!property_exists($this, $property)) {
                // まだ未対応の強化項目が来た場合、エラーーとならないようにする
                continue;
            }
            $maxLevel = $mstOutpostEnhancement->mst_outpost_enhancement_level?->first()->max_level ?? 0;
            $components[] = TextInput::make($property)
                ->label(OutpostEnhancementType::tryFrom($enhancementType)?->label() ?? $enhancementType)
                ->numeric()
                ->minValue(1)
                ->maxValue($maxLevel);
        }
        return $components;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        // 原画の更新
        $state = $this->form->getState();
        UsrOutpost::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->update([
                'mst_artwork_id' => $state['mstArtworkId'],
            ]);

        // 各強化項目の更新
        $mstOutpostEnhancements = MstOutpostEnhancement::query()
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->get();
        $usrOutpostEnhancements = UsrOutpostEnhancement::query()
            ->with(['mst_outpost_enhancement'])
            ->where('usr_user_id', $this->userId)
            ->where('mst_outpost_id', $this->mstOutpostId)
            ->get()
            ->keyBy(function (UsrOutpostEnhancement $record) {
                return $record->mst_outpost_enhancement_id;
            });

        $upsertData = [];
        foreach ($mstOutpostEnhancements as $mstOutpostEnhancement) {
            $currentLevel = $usrOutpostEnhancements->get($mstOutpostEnhancement->id)?->level ?? 1;
            $property = $this->convertOutpostEnhancementTypeToProperty($mstOutpostEnhancement->outpost_enhancement_type);

            $setLevel = $state[$property];
            if ($currentLevel !== $setLevel) {
                // 更新がある場合
                $upsertData[] = [
                    'usr_user_id' => $this->userId,
                    'mst_outpost_id' => $this->mstOutpostId,
                    'mst_outpost_enhancement_id' => $mstOutpostEnhancement->id,
                    'level' => $setLevel,
                ];
            }
        }

        if (count($upsertData) > 0) {
            UsrOutpostEnhancement::query()->upsert(
                $upsertData,
                ['usr_user_id','mst_outpost_id','mst_outpost_enhancement_id'],
                ['level']
            );
        }
        $this->redirectRoute('filament.admin.pages.user-outpost', ['userId' => $this->userId]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->requiresConfirmation()
                ->action(fn () => $this->update())
        ];
    }

    private function convertOutpostEnhancementTypeToProperty(string $outpostEnhancementType)
    {
        return lcfirst($outpostEnhancementType);
    }
}
