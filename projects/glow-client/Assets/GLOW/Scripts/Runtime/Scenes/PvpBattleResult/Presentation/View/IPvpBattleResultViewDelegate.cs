namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public interface IPvpBattleResultViewDelegate
    {
        void OnViewDidAppear();
        void OnUnloadView();
        void OnCloseButtonTapped();
        void OnActionButtonTapped();
    }
}