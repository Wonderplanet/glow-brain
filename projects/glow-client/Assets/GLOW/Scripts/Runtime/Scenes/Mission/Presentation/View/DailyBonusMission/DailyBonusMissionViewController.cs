using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.View.DailyBonusMission
{
    public class DailyBonusMissionViewController :
        UIViewController<DailyBonusMissionView>, 
        IEscapeResponder,
        IAsyncActivityControl
    {
        public record Argument(IDailyBonusMissionViewModel ViewModel, Action<IDailyBonusMissionViewModel> OnReceivedAction);
        [Inject] IDailyBonusMissionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IMissionMainControl MissionMainViewControl { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetViewModel(IDailyBonusMissionViewModel viewModel)
        {
            ActualView.SetUp(viewModel, OnRewardIconSelected);
        }
        
        public async UniTask PlayDailyBonusStampAnimation(
            CancellationToken cancellationToken, 
            LoginDayCount loginDayCount)
        {
            await ActualView.PlayDailyBonusStampAnimationAsync(cancellationToken, loginDayCount);
        }

        public void UpdateMissionNextUpdateTime(RemainingTimeSpan nextUpdateTime)
        {
            ActualView.UpdateTime(nextUpdateTime);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
                return false;

            UISoundEffector.Main.PlaySeEscape();
            ViewDelegate.OnEscape();
            return true;
        }
        
        void IAsyncActivityControl.ActivityBegin()
        {
            View.UserInteraction = false;
            MissionMainViewControl.SetInteractable(false);
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            View.UserInteraction = true;
            MissionMainViewControl.SetInteractable(true);
        }

        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ViewDelegate.OnRewardIconSelected(playerResourceIconViewModel);
        }
    }
}
