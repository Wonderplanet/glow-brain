using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.Navigation
{

    public interface IMissionNavigator
    {
        void ShowHomeView();
        void ShowHomeQuestSelectView();
        void ShowIdleIncentiveTopView();
        void ShowUrl(CriterionValue url);
        void ShowUnitListView();
        void ShowOutpostEnhanceView();
        void ShowGachaView(MasterDataId gachaId);
        void ShowContentTopView();
        void ShowPvpTopView();
        void ShowLinkBnIdView();
    }
}
