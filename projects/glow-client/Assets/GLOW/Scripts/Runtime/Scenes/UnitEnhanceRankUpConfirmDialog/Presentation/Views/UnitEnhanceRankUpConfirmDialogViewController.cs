using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views
{
    public class UnitEnhanceRankUpConfirmDialogViewController : UIViewController<UnitEnhanceRankUpConfirmDialogView>
    {
        public record Argument(UserDataId UserUnitId, Action OnConfirm);

        [Inject] IUnitEnhanceRankUpConfirmDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
        }

        public void Setup(UnitEnhanceRankUpConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }
        
        public void PlayCostItemAppearanceAnimation()
        {
            ActualView.PlayCostItemAppearanceAnimation();
        }

        [UIAction]
        void OnConfirmButtonTapped()
        {
            ViewDelegate.OnConfirmButtonTapped();
        }

        [UIAction]
        void OnCancelButtonTapped()
        {
            ViewDelegate.OnCancelButtonTapped();
        }

        [UIAction]
        void OnRankUpDetailButtonTapped()
        {
            ViewDelegate.OnRankUpDetailButtonTapped();
        }
    }
}
