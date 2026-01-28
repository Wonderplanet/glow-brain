using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views
{
    public class UnitEnhanceGradeUpConfirmDialogViewController : UIViewController<UnitEnhanceGradeUpConfirmDialogView>
    {
        public record Argument(UserDataId UserUnitId, Action OnConfirm);

        [Inject] IUnitEnhanceGradeUpConfirmDialogViewDelegate ViewDelegate { get; }

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

        public void Setup(UnitEnhanceGradeUpConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel);
            ActualView.SetSpecialAttackPreview(viewModel.SpecialAttackName, viewModel.Description);
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
        void OnGradeUpDetailButtonTapped()
        {
            ViewDelegate.OnGradeUpDetailButtonTapped();
        }
    }
}
