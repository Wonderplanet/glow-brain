using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public interface IEventQuestSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnEventQuestButtonTapped(MasterDataId mstQuestGroupId);
        void OnBackButtonTapped();
        void OnMissionButtonTapped();
        void UpdateMissionBadge();
        void OnAdventBattleButtonTapped();
        void ShowEventExchangeShop();
    }
}
