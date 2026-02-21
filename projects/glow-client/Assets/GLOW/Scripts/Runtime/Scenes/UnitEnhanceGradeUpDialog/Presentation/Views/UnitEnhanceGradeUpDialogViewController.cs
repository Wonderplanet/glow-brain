using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views
{
    public class UnitEnhanceGradeUpDialogViewController : UIViewController<UnitEnhanceGradeUpDialogView>
    {
        public record Argument(
            UserDataId UserUnitId,
            UnitGrade BeforeGrade,
            UnitGrade AfterGrade);

        [Inject] IUnitEnhanceGradeUpDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(UnitEnhanceGradeUpDialogViewModel viewModel)
        {
            ActualView.Setup(viewModel);
            ActualView.SetSpecialAttackPreview(viewModel.SpecialAttackName, viewModel.Description);
        }

        public void AnimationEnded()
        {
            ActualView.AnimationEnded();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}
