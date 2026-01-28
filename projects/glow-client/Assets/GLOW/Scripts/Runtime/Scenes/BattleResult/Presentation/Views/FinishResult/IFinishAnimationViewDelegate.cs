namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    public interface IFinishAnimationViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnAnimationCompleted();
        void OnCloseSelected();
    }
}
