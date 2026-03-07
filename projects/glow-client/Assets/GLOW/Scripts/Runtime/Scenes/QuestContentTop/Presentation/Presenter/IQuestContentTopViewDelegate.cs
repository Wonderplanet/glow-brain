using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public interface IQuestContentTopViewDelegate
    {
        void OnViewWillAppear();
        void OnViewDidUnload();
        void Refresh();
        void OnEventButtonTapped(MasterDataId mstEventId);
        void OnEnhanceButtonTapped();
        void OnRaidButtonTapped();
        void OnLimitedButtonTapped();
        void OnPvpButtonTapped();
        void OnRankingButtonTapped();
    }
}
