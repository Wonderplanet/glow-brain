using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Modules.InAppReview.Domain.ValueObject;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeMainViewDelegate : IHomeMainStageSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewWillDisappear();
        void OnItemBoxSelected();
        void OnDeckButtonEdit(MasterDataId selectedStageId);
        void OnIdleIncentiveSelected();

        void OnEventMissionSelected();
        void OnArtworkPanelMissionSelected();
        void OnBeginnerMissionSelected();
        void OnNormalMissionSelected(MissionType missionType = MissionType.Daily, bool isDisplayFromItemDetail = false);
        void OnMainQuestSelected();
        void OnQuestSelectedWithId(MasterDataId questId);
        void OnMenuSelected();
        void OnBnIdLinkSelected();
        void OnAnnouncementButtonSelected();
        void OnMessageBoxButtonSelected();
        UniTask ShowQuestReleaseView(
            ShowQuestReleaseAnimation showQuestReleaseAnimation,
            InAppReviewFlag isInAppReviewDisplay,
            CancellationToken cancellationToken);
        void OnEncyclopediaTapped();
        void OnLatestEventTapped();
        void OnEndContentButtonTapped();
        void OnInGameSpecialRuleTapped(MasterDataId selectedMstStageRuleGroupId);
        void OnComeBackDailyBonusButtonTapped();
        void OnExchangeContentTopButtonTapped();
        void OnExchangeShopTopSelected(MasterDataId mstExchangeId);
        void OnPvpButtonTapped();
        void OnHomeMainKomaSettingButtonTapped();
    }
}
