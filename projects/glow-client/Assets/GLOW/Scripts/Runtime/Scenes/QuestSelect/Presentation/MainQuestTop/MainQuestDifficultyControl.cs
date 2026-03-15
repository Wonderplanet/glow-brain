using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public class MainQuestDifficultyControl
    {
        readonly MasterDataId _mstGroupQuestId; //ここではない気もするが難易度変更のときにしか使わないのでここに置く
        public MasterDataId MstGroupQuestId => _mstGroupQuestId;

        readonly QuestDifficultyButtonListComponent _questDifficultyButtonListComponent;

        // ここにキャンペーンあるの、いまいちかもしれない。難易度の子要素だからここに一旦置いている
        // キャンペーンバルーン
        CampaignBalloonMultiSwitcherComponent _normalCampaignBalloonSwitcher;
        CampaignBalloonMultiSwitcherComponent _hardCampaignBalloonSwitcher;
        CampaignBalloonMultiSwitcherComponent _extraCampaignBalloonSwitcher;
        bool _isCampaignBalloonHidden;

        public MainQuestDifficultyControl(
            MasterDataId mstGroupQuestId,
            QuestDifficultyButtonListComponent questDifficultyButtonListComponent,
            CampaignBalloonMultiSwitcherComponent normalCampaignBalloonSwitcher,
            CampaignBalloonMultiSwitcherComponent hardCampaignBalloonSwitcher,
            CampaignBalloonMultiSwitcherComponent extraCampaignBalloonSwitcher)
        {
            _mstGroupQuestId = mstGroupQuestId;
            _questDifficultyButtonListComponent = questDifficultyButtonListComponent;
            _normalCampaignBalloonSwitcher = normalCampaignBalloonSwitcher;
            _hardCampaignBalloonSwitcher = hardCampaignBalloonSwitcher;
            _extraCampaignBalloonSwitcher = extraCampaignBalloonSwitcher;
        }

        public void InitializeDifficultyButtons(
            Difficulty currentDifficulty,
            IReadOnlyList<QuestDifficultyItemViewModel> questDifficultyItemViewModels,
            IReadOnlyList<CampaignViewModel> normalCampaignViewModels,
            IReadOnlyList<CampaignViewModel> hardCampaignViewModels,
            IReadOnlyList<CampaignViewModel> extraCampaignViewModels)
        {
            _questDifficultyButtonListComponent.InitializeView();

            // Difficultyは貰った時点で開放されている難易度であることを保証している
            // すべて開放「されていない」とき、selectedDifficultyでNormalがくる
            var selectedDifficulty = currentDifficulty;

            var normalViewModel = questDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Normal,
                QuestDifficultyItemViewModel.Empty);

            var hardViewModel = questDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Hard,
                QuestDifficultyItemViewModel.Empty);

            var extraViewModel = questDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Extra,
                QuestDifficultyItemViewModel.Empty);

            // 表示更新
            var shouldShowNormalButton = !normalViewModel.IsEmpty();
            _questDifficultyButtonListComponent.NormalButton.IsVisible = shouldShowNormalButton;

            if (shouldShowNormalButton)
            {
                _questDifficultyButtonListComponent.NormalButton.Setup(selectedDifficulty, normalViewModel);
            }

            var shouldShowHardButton = !hardViewModel.IsEmpty();
            _questDifficultyButtonListComponent.HardButton.IsVisible = shouldShowHardButton;

            if (shouldShowHardButton)
            {
                _questDifficultyButtonListComponent.HardButton.Setup(selectedDifficulty, hardViewModel);
            }

            var shouldShowExtraButton = !extraViewModel.IsEmpty();
            _questDifficultyButtonListComponent.ExtraButton.IsVisible = shouldShowExtraButton;

            if (shouldShowExtraButton)
            {
                _questDifficultyButtonListComponent.ExtraButton.Setup(selectedDifficulty, extraViewModel);
            }

            _questDifficultyButtonListComponent.UpdateButtonLayoutGroup();

            // キャンペーンバルーン
            SetUpCampaignBalloons(
                normalCampaignViewModels,
                hardCampaignViewModels,
                extraCampaignViewModels);
        }

        void SetUpCampaignBalloons(
            IReadOnlyList<CampaignViewModel> normalCampaignViewModels,
            IReadOnlyList<CampaignViewModel> hardCampaignViewModels,
            IReadOnlyList<CampaignViewModel> extraCampaignViewModels)
        {
            if (_isCampaignBalloonHidden)
            {
                _normalCampaignBalloonSwitcher.Hidden = true;
                _hardCampaignBalloonSwitcher.Hidden = true;
                _extraCampaignBalloonSwitcher.Hidden = true;
                return;
            }
            _normalCampaignBalloonSwitcher.SetUpCampaignBalloons(normalCampaignViewModels);
            _hardCampaignBalloonSwitcher.SetUpCampaignBalloons(hardCampaignViewModels);
            _extraCampaignBalloonSwitcher.SetUpCampaignBalloons(extraCampaignViewModels);
        }

        // チュートリアル向け
        public void SetActiveCampaignBalloon(bool isActive)
        {
            _isCampaignBalloonHidden = !isActive;

            _normalCampaignBalloonSwitcher.Hidden = !isActive;
            _hardCampaignBalloonSwitcher.Hidden = !isActive;
            _extraCampaignBalloonSwitcher.Hidden = !isActive;
        }
    }
}
