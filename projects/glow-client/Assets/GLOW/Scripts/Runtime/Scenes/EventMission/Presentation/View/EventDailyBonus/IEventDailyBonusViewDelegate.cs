using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus
{
    public interface IEventDailyBonusViewDelegate
    {
        void OnViewDidLoad();
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnEscape();
        
    }
}