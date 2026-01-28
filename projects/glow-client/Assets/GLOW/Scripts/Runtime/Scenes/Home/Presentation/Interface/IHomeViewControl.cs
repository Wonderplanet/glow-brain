using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Constants;

namespace GLOW.Scenes.Home.Presentation.Interface
{
    public interface IHomeViewControl
    {
        // HomeContentTypes / (detailView)
        // Main/クエスト
        void OnQuestSelected();
        void OnQuestSelectedFromHome(MasterDataId masterDataId, bool popBeforeDetail = false);
        // Main/放置収益
        void OnIdleIncentiveTopSelected();
        // Main/ミッション
        void OnNormalMissionSelected();
        void OnNormalMissionSelectedFromHome(MissionType missionType, bool isDisplayFromItemDetail);

        // Main/交換所
        void OnExchangeContentTopSelected();
        void OnExchangeShopTopSelected(MasterDataId mstExchangeId);

        // Character
        void OnUnitListSelected();
        void OnOutpostEnhanceSelected();
        void OnPartyFormationSelected();

        // Gacha
        void OnGachaSelected();
        void OnGachaContentSelectedFromHome(MasterDataId gachaId);

        // Content
        void OnContentTopSelected();
        void OnEventQuestSelectedFromHome(MasterDataId mstEventId);
        void OnPvpTopSelected();

        // Shop
        void OnShopSelectedFromHome(ShopContentTypes shopContentType, MasterDataId masterDataId);
        void OnBasicShopSelected();
        void OnPackShopSelected();
        void OnPassShopSelected();

        // Menu
        void OnLinkBnIdSelected();
    }
}
