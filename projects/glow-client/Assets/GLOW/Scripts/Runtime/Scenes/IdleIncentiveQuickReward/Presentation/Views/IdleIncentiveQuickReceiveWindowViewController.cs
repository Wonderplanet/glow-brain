using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views
{
    public class IdleIncentiveQuickReceiveWindowViewController : UIViewController<IdleIncentiveQuickReceiveWindowView>
    {
        [Inject] IIdleIncentiveQuickReceiveWindowViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }
        
        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.ViewDidAppear();
        }

        public void Setup(IdleIncentiveQuickReceiveWindowViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }
        
        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        public void UpdateQuickAdReceiveInterval(
            string intervalMinute, 
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            ActualView.UpdateQuickAdReceiveInterval(intervalMinute, heldAdSkipPassInfoViewModel);
        }
        [UIAction]
        void OnReceiveAtAdButtonTapped()
        {
            ViewDelegate.OnReceiveByAd();
        }

        [UIAction]
        void OnReceiveAtItemButtonTapped()
        {
            ViewDelegate.OnReceiveAtDiamond();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}
