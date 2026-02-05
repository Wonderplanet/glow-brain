using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ComebackDailyBonus.Presentation.View
{
    public interface IComebackDailyBonusViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseButtonSelected();
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
    }
}