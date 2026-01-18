using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyNameEdit.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PartyNameEdit.Presentation.Views
{
    public class PartyNameEditDialogViewController : UIViewController<PartyNameEditDialogView>
    {
        public record Argument(PartyNo PartyNo);

        [Inject] IPartyNameEditDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void SetPartyName(PartyNameEditDialogViewModel viewModel)
        {
            ActualView.SetPartyName(viewModel.PartyName);
        }

        public void ShowInvalidNameMessage()
        {
            ActualView.ShowInvalidNameMessage();
        }

        [UIAction]
        void OnSaveButtonClicked()
        {
            ViewDelegate.OnSaveButtonClicked(ActualView.InputText);
        }

        [UIAction]
        void OnCloseButtonClicked()
        {
            ViewDelegate.OnCloseButtonClicked();
        }
    }
}
