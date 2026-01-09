using System;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views.DefeatDialog
{
    public class DefeatDialogViewController : UIViewController<DefeatDialogView>
    {
        public record Argument(DefeatDialogViewModel ViewModel);

        [Inject] IDefeatDialogViewDelegate ViewDelegate { get; }

        public Action OnCloseAction { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUp(DefeatDialogViewModel viewModel)
        {
            ActualView.SetDescription(viewModel.Description);
        }

        public void OnClose()
        {
            OnCloseAction?.Invoke();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}
