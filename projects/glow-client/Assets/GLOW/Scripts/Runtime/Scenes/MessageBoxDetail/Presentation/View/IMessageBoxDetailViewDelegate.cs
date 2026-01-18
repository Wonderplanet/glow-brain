namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public interface IMessageBoxDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnClose();
        void OnOpenSelected();
    }
}