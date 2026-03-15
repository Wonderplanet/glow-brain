namespace GLOW.Scenes.InGame.Presentation.Views
{
    public interface IInGameStartAnimationViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnAnimationCompleted();
    }
}
