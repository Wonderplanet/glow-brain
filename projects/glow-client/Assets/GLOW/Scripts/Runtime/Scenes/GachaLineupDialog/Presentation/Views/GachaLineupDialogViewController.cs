using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views
{
    public class GachaLineupDialogViewController : UIViewController<GachaLineupDialogView>, IEscapeResponder
    {
        public record Argument(MasterDataId OprGachaId, GachaLineupDialogViewModel ViewModel);

        [Inject] IGachaLineupDialogDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        GachaRatioTabType _currentTab = GachaRatioTabType.NormalRatioTab;
        public GachaRatioTabType CurrentTab => _currentTab;

        public GachaLineupDialogViewController.Argument Args { get; set; }
        public float NormalizedPos => ActualView.ScrollRect.verticalNormalizedPosition;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void Setup(GachaLineupDialogViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }
        
        public void GachaRatioPageSetUp(GachaRatioTabType type)
        {
            ActualView.GachaRatioPageUpdate(type);
            _currentTab = type;
        }

        public void GachaRatioPageUpdate(GachaRatioTabType type)
        {
            if (_currentTab == type) return;
            
            ActualView.GachaRatioPageUpdate(type);
            _currentTab = type;
        }

        public void MoveScrollToTargetPos(float targetPos)
        {
            ActualView.MoveScrollToTargetPos(targetPos);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }
        
        void Close()
        {
            ViewDelegate.OnClosed();
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            Close();
        }

        [UIAction]
        public void OnNormalRatioButtonTapped()
        {
            ViewDelegate.OnNormalRatioTabSelected();
        }

        [UIAction]
        public void OnSSRRatioButtonTapped()
        {
            ViewDelegate.OnSSRRatioTabSelected();
        }

        [UIAction]
        public void OnURRatioButtonTapped()
        {
            ViewDelegate.OnURRatioTabSelected();
        }

        [UIAction]
        public void OnPickupRatioButtonTapped()
        {
            ViewDelegate.OnPickupRatioTabSelected();
        }
    }
}