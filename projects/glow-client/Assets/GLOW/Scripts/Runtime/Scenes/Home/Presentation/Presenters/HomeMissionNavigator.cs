using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Mission.Presentation.Navigation;
using WonderPlanet.OpenURLExtension;
using WPFramework.Exceptions;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMissionNavigator : IMissionNavigator
    {
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] GachaListUseCase GachaListUseCase { get; }
        [Inject] SkipEventDailyBonusAnimationUseCase SkipEventDailyBonusAnimationUseCase { get; }

        void IMissionNavigator.ShowHomeView()
        {
            NotImpl.Handle();
        }

        void IMissionNavigator.ShowHomeQuestSelectView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnQuestSelected();
        }

        void IMissionNavigator.ShowIdleIncentiveTopView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnIdleIncentiveTopSelected();
        }

        void IMissionNavigator.ShowUrl(
            CriterionValue url)
        {
            CustomOpenURL.OpenURL(url.Value);
        }

        void IMissionNavigator.ShowUnitListView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnUnitListSelected();
        }

        void IMissionNavigator.ShowOutpostEnhanceView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnOutpostEnhanceSelected();
        }

        void IMissionNavigator.ShowGachaView(MasterDataId gachaId)
        {
            if (gachaId.IsEmpty())
            {
                // 設定なしの場合はTOPまで
                ShowGachaTopView();
                return;
            }

            var gachaList = GachaListUseCase.UpdateAndGetGachaListUseCaseModel();
            if(gachaList.PremiumGachaModel.GachaId.Value == gachaId.Value)
            {
                // プレミアムの場合はTOPまで
                ShowGachaTopView();
                return;
            }

            var isFestival = gachaList.FestivalBannerModels.Any(x => x.GachaId.Value == gachaId.Value);
            var isPickup = gachaList.PickupBannerModels.Any(x => x.GachaId.Value == gachaId.Value);
            if(isFestival || isPickup)
            {
                // ピックアップ、フェスガチャの場合は詳細まで
                ShowGachaDetailTopView(new MasterDataId(gachaId.Value));
                return;
            }

            // どれにも該当しない場合はガチャTOPへ
            ShowGachaTopView();
        }

        void IMissionNavigator.ShowContentTopView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnContentTopSelected();
        }

        void IMissionNavigator.ShowPvpTopView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnPvpTopSelected();
        }

        void IMissionNavigator.ShowLinkBnIdView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnLinkBnIdSelected();
        }

        void ShowGachaTopView()
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnGachaSelected();
        }

        void ShowGachaDetailTopView(MasterDataId gachaId)
        {
            SkipEventDailyBonusAnimationUseCase.ClearEventDailyBonusRewardModels();
            HomeViewControl.OnGachaContentSelectedFromHome(gachaId);
        }
    }
}
