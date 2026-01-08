using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public interface IPvpTopViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewDidUnLoad();

        void OnRankingButtonTapped();
        void OnRewardListButtonTapped();
        void OnStageDetailButtonTapped();
        void OnBattleStartTapped();
        void OnPartyEditTapped();
        void OnBackButtonTapped();
        void OnHelpButtonTapped();
        bool IsStartBattle();

        //対戦相手・リフレッシュ
        void OnOpponentRefreshButtonTapped();
        void OnOpponentTapped(PvpOpponentNumber number);
        void OnOpponentInfoButtonTapped(int index);
    }
}
