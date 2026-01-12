using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class DummyHomeViewControlInInGame : IHomeViewControl
    {
        void IHomeViewControl.OnQuestSelected()
        {
        }

        void IHomeViewControl.OnQuestSelectedFromHome(MasterDataId masterDataId, bool popBeforeDetail)
        {
        }

        void IHomeViewControl.OnIdleIncentiveTopSelected()
        {
        }

        void IHomeViewControl.OnUnitListSelected()
        {
        }

        void IHomeViewControl.OnOutpostEnhanceSelected()
        {
        }

        void IHomeViewControl.OnNormalMissionSelected()
        {
        }

        void IHomeViewControl.OnNormalMissionSelectedFromHome(MissionType missionType, bool isDisplayFromItemDetail)
        {
        }

        void IHomeViewControl.OnExchangeContentTopSelected()
        {
        }

        void IHomeViewControl.OnExchangeShopTopSelected(MasterDataId mstExchangeId)
        {
        }

        void IHomeViewControl.OnBasicShopSelected()
        {
        }

        public void OnItemExchangeShopSelected()
        {
        }

        void IHomeViewControl.OnPackShopSelected()
        {
        }

        void IHomeViewControl.OnPassShopSelected()
        {
        }

        void IHomeViewControl.OnLinkBnIdSelected()
        {
        }

        void IHomeViewControl.OnPartyFormationSelected()
        {
        }

        void IHomeViewControl.OnPvpTopSelected()
        {
        }

        void IHomeViewControl.OnShopSelectedFromHome(ShopContentTypes shopContentType, MasterDataId masterDataId)
        {
        }

        void IHomeViewControl.OnGachaSelected()
        {
        }

        void IHomeViewControl.OnGachaContentSelectedFromHome(MasterDataId gachaId)
        {
        }

        void IHomeViewControl.OnContentTopSelected()
        {
        }

        void IHomeViewControl.OnEventQuestSelectedFromHome(MasterDataId mstQuestId)
        {
        }
    }
}
