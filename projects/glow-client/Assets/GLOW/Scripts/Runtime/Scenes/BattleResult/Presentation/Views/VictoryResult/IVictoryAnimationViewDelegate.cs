namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public interface IVictoryAnimationViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnAnimationCompleted();
        void OnCloseSelected();
    }
}
