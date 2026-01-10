namespace GLOW.Scenes.PartyNameEdit.Presentation.Views
{
    public interface IPartyNameEditDialogViewDelegate
    {
        void ViewDidLoad();
        void OnSaveButtonClicked(string newPartyName);
        void OnCloseButtonClicked();
    }
}
