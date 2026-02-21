namespace GLOW.Scenes.UserNameEdit.Presentation.Views
{
    public interface IUserNameEditDialogViewDelegate
    {
        void ViewDidLoad();
        void OnSaveButtonTapped(string newUserName);
        void OnCloseButtonTapped();
    }
}
