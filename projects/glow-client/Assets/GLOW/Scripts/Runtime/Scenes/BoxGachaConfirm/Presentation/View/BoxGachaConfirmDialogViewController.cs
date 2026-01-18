using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.BoxGachaConfirm.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.View
{
    public class BoxGachaConfirmDialogViewController : UIViewController<BoxGachaConfirmDialogView>,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstBoxGachaId);
        
        [Inject] IBoxGachaConfirmDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        public Action<GachaDrawCount> OnDrawButtonTappedAction { get; set; }
        
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

        public void SetUpConfirmDialog(BoxGachaConfirmDialogViewModel viewModel)
        {
            ActualView.Initialize(
                viewModel.CostItemName,
                viewModel.OfferCostItemAmount,
                viewModel.CostItemAmount,
                viewModel.BoxGachaName);
            ActualView.SetUpTitle(viewModel.BoxGachaName);
            ActualView.SetUpConfirmText(
                viewModel.CostItemName,
                viewModel.CostItemAmount,
                viewModel.BoxGachaName,
                new GachaDrawCount(1));
            ActualView.SetUpCostItemIcon(viewModel.CostItemIconAssetPath);
            ActualView.SetUpOfferCostItemAmountDisplay(
                viewModel.OfferCostItemAmount,
                viewModel.CostItemAmount,
                new GachaDrawCount(1));
            ActualView.SetUpShortageAttentionTextAndButton(
                viewModel.CostItemName,
                viewModel.IsDrawable);
            ActualView.SetUpAmountSelection(
                viewModel.CanSelectDrawCount);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            
            ViewDelegate.OnCancelButtonTapped();
            return true;
        }

        [UIAction]
        void OnCancelButtonTapped()
        {
            ViewDelegate.OnCancelButtonTapped();
        }

        [UIAction]
        void OnDrawButtonTapped()
        {
            var drawCount = ActualView.GachaDrawCount;
            ViewDelegate.OnDrawButtonTapped(drawCount);
        }
    }
}