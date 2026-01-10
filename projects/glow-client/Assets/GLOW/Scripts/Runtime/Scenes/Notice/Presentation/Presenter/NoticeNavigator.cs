using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Notice.Presentation.Navigation;
using WonderPlanet.OpenURLExtension;
using Zenject;

namespace GLOW.Scenes.Notice.Presentation.Presenter
{
    public class NoticeNavigator : INoticeNavigator
    {
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] GachaListUseCase GachaListUseCase { get; }
        [Inject] GetActiveExchangeShopLineupIdUseCase GetActiveExchangeShopLineupIdUseCase { get; }

        void INoticeNavigator.ShowBasicShopTopView()
        {
            HomeViewControl.OnBasicShopSelected();
        }

        void INoticeNavigator.ShowPackShopTopView()
        {
            HomeViewControl.OnPackShopSelected();
        }

        void INoticeNavigator.ShowPassShopTopView()
        {
            HomeViewControl.OnPassShopSelected();
        }

        void INoticeNavigator.ShowGachaView(NoticeDestinationPathDetail pathDetail)
        {
            if (pathDetail.IsEmpty())
            {
                // 設定なしの場合はTOPまで
                ShowGachaTopView();
                return;
            }

            var gachaList = GachaListUseCase.UpdateAndGetGachaListUseCaseModel();
            if(gachaList.PremiumGachaModel.GachaId.Value == pathDetail.Value)
            {
                // プレミアムの場合はTOPまで
                ShowGachaTopView();
                return;
            }

            var isFestival = gachaList.FestivalBannerModels.Any(x => x.GachaId.Value == pathDetail.Value);
            var isPickup = gachaList.PickupBannerModels.Any(x => x.GachaId.Value == pathDetail.Value);
            if(isFestival || isPickup)
            {
                // ピックアップ、フェスガチャの場合は詳細まで
                ShowGachaDetailTopView(new MasterDataId(pathDetail.Value));
                return;
            }

            // どれにも該当しない場合はガチャTOPへ
            ShowGachaTopView();
        }

        void INoticeNavigator.ShowContentTopView()
        {
            HomeViewControl.OnContentTopSelected();
        }

        void INoticeNavigator.ShowPvpTopView()
        {
            HomeViewControl.OnPvpTopSelected();
        }

        void INoticeNavigator.ShowUrl(DestinationScene destinationScene)
        {
            CustomOpenURL.OpenURL(destinationScene.Value);
        }

        void INoticeNavigator.ShowExchangeShopView(NoticeDestinationPathDetail pathDetail)
        {
            // ID指定なし、交換所一覧へ遷移
            if (pathDetail.IsEmpty())
            {
                HomeViewControl.OnExchangeContentTopSelected();
                return;
            }

            HomeViewControl.OnExchangeShopTopSelected(pathDetail.ToMasterDataId());
        }

        void ShowGachaTopView()
        {
            HomeViewControl.OnGachaSelected();
        }

        void ShowGachaDetailTopView(MasterDataId gachaId)
        {
            HomeViewControl.OnGachaContentSelectedFromHome(gachaId);
        }
    }
}
