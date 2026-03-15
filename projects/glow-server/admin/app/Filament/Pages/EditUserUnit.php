<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstUnit;
use App\Models\Mst\MstUnitGradeUp;
use App\Models\Mst\MstUnitLevelUp;
use App\Models\Mst\MstUnitRankUp;
use App\Models\Usr\UsrUnit;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserUnit extends UserDataBasePage
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::UNIT->value;

    public string $unitId = '';

    protected $queryString = [
        'userId',
        'unitId',
    ];

    public int $level;
    public int $rank;
    public int $gradeLevel;
    public int $lastRewardGradeLevel;

    public function mount()
    {
        parent::mount();
        $userParameter = UsrUnit::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_unit_id', $this->unitId)
            ->first();

        $this->level = $userParameter->level;
        $this->rank = $userParameter->rank;
        $this->gradeLevel = $userParameter->grade_level;
        $this->lastRewardGradeLevel = $userParameter->last_reward_grade_level;

        $this->form->fill([
            'level' => $this->level,
            'rank' => $this->rank,
            'gradeLevel' => $this->gradeLevel,
            'lastRewardGradeLevel' => $this->lastRewardGradeLevel,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserUnit::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'unitId' => $this->unitId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        $mstUnit = MstUnit::query()->where('id', $this->unitId)->first();
        $maxLevel = MstUnitLevelUp::query()->where('unit_label', $mstUnit->unit_label)->max('level');
        $maxRank = MstUnitRankUp::query()->where('unit_label', $mstUnit->unit_label)->max('rank');
        $maxGrade = MstUnitGradeUp::query()->where('unit_label', $mstUnit->unit_label)->max('grade_level');

        return [
            TextInput::make('level')->label('レベル')->numeric()->minValue(1)->maxValue($maxLevel),
            TextInput::make('rank')->label('ランク')->numeric()->minValue(0)->maxValue($maxRank),
            TextInput::make('gradeLevel')->label('グレード')->numeric()->minValue(1)->maxValue($maxGrade),
            TextInput::make('lastRewardGradeLevel')->label('最終報酬受取グレード')->numeric()->minValue(0)->maxValue($maxGrade),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrUnit::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_unit_id', $this->unitId)
            ->update([
                'level' => $this->form->getState()['level'],
                'rank' => $this->form->getState()['rank'],
                'grade_level' => $this->form->getState()['gradeLevel'],
                'last_reward_grade_level' => $this->form->getState()['lastRewardGradeLevel'],
            ]);
        $this->redirectRoute('filament.admin.pages.user-unit', ['userId' => $this->userId]);
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
}
