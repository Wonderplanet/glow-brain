using GLOW.Scenes.Login.Domain.Constants.Login;

namespace GLOW.Scenes.Title.Presentations.Views
{
    public interface ITitleViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnMenuSelected();
        void OnEscapeSelected(LoginPhases loginPhases);
    }
}
