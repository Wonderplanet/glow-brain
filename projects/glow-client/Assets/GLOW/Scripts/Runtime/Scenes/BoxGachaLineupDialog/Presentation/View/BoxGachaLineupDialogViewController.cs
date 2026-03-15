using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.View
{
    public class BoxGachaLineupDialogViewController : 
        UIViewController<BoxGachaLineupDialogView>,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstBoxGachaId, BoxLevel CurrentBoxLevel);
        [Inject] IBoxGachaLineupDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
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
        
        public void SetUpViewModel(BoxGachaLineupDialogViewModel viewModel)
        {
            ActualView.SetUpTitle(viewModel.BoxResetCount);
            ActualView.SetUpURLineupList(viewModel.URBoxGachaLineupListViewModel, OnPrizeIconSelected);
            ActualView.SetUpSSRLineupList(viewModel.SSRBoxGachaLineupListViewModel, OnPrizeIconSelected);
            ActualView.SetUpSRLineupList(viewModel.SRBoxGachaLineupListViewModel, OnPrizeIconSelected);
            ActualView.SetUpRLineupList(viewModel.RBoxGachaLineupListViewModel, OnPrizeIconSelected);
            ActualView.SetUpUnitDetailAttentionTextVisible(viewModel.IsUnitContainInLineup);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }
        
        void OnPrizeIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ViewDelegate.OnPrizeIconTapped(playerResourceIconViewModel);
        }

        void Close()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
        
        [UIAction]
        void OnCloseButtonTapped()
        {
            Close();
        } 
    }
}