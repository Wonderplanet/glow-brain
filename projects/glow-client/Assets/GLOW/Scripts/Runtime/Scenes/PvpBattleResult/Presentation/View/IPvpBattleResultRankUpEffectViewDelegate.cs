namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public interface IPvpBattleResultRankUpEffectViewDelegate
    {
        void OnViewDidAppear();
        void OnUnloadView();
        void OnCloseButtonTapped();
        void OnSkipButtonTapped();
    }
}