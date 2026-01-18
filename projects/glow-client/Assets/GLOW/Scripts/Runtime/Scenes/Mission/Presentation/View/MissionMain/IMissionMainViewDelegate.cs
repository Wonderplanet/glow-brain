using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel;

namespace GLOW.Scenes.Mission.Presentation.View.MissionMain
{
    public interface IMissionMainViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnDailyBonusMissionTabSelected();
        void OnDailyMissionTabSelected();
        void OnWeeklyMissionTabSelected();
        void OnAchievementMissionTabSelected();
        void OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel);
        UniTask<IMissionViewModel> FetchMissionList(CancellationToken cancellationToken);
        void OnEscape();
    }
}
