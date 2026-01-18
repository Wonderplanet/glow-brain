using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeMainViewControl
    {
        void OnQuestSelected();
        void OnQuestSelectedWithId(MasterDataId questId);
        void OnIdleIncentiveTopSelected();
        void OnCharacterListSelected();
        void OnMissionSelected();
        void OnMissionSelectedForType(MissionType missionType, bool isDisplayFromItemDetail);
        void OnBnIdLinkSelected();
        void OnExchangeContentTopSelected();
        void OnExchangeShopTopSelected(MasterDataId mstLineupId);
    }
}
