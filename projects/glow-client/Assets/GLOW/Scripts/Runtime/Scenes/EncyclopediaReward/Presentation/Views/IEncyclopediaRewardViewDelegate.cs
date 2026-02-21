using System.Collections.Generic;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    public interface IEncyclopediaRewardViewDelegate
    {
        void OnViewWillAppear();
        void OnSelectLockReward(EncyclopediaRewardListCellViewModel cellViewModel);
        void OnSelectReward(EncyclopediaRewardListCellViewModel cellViewModel);
        void OnShowEncyclopediaEffectButtonTapped();
        void OnReceiveAllRewardButtonTapped(IReadOnlyList<EncyclopediaRewardListCellViewModel> cellViewModels);
        void OnBackButtonTapped();
        void OnBackToHomeButtonTapped();
    }
}
