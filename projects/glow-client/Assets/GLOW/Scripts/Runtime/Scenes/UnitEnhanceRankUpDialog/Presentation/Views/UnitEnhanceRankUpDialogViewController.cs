using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views
{
    public class UnitEnhanceRankUpDialogViewController : UIViewController<UnitEnhanceRankUpDialogView>
    {
        public record Argument(UserDataId UserUnitId,
            UnitRank BeforeRank,
            UnitRank AfterRank);

        [Inject] IUnitEnhanceRankUpDialogViewDelegate ViewDelegate { get; }

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

        public void Setup(UnitEnhanceRankUpDialogViewModel viewModel)
        {
            ActualView.Setup(viewModel);
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
