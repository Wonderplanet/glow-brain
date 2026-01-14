using System;
using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Modules.CommonWebView.Domain.UseCase;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.ToastNotifier;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainBannerItemPresenter : IHomeMainBannerItemViewDelegate
    {
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] IHomeMainViewDelegate HomeMainViewDelegate { get; }
        [Inject] GetMyIdUseCase GetMyIdUseCase { get; }
        void IHomeMainBannerItemViewDelegate.OnBannerClicked(
            HomeBannerDestinationType destinationType,
            HomeBannerDestinationPath destinationPath)
        {
            switch (destinationType)
            {
                case HomeBannerDestinationType.Gacha:
                    if (destinationPath.IsEmpty())
                    {
                        HomeViewControl.OnGachaSelected();
                    }
                    else
                    {
                        HomeViewControl.OnGachaContentSelectedFromHome(destinationPath.ToMstGachaId());
                    }
                    break;
                case HomeBannerDestinationType.Pack:
                    HomeViewControl.OnPackShopSelected();
                    break;
                case HomeBannerDestinationType.BeginnerMission:
                    HomeMainViewDelegate.OnBeginnerMissionSelected();
                    break;
                case HomeBannerDestinationType.Event:
                    HomeViewControl.OnEventQuestSelectedFromHome(destinationPath.ToMstEventId());
                    break;
                case HomeBannerDestinationType.Pass:
                    HomeViewControl.OnPassShopSelected();
                    break;
                case HomeBannerDestinationType.Pvp:
                    HomeViewControl.OnPvpTopSelected();
                    break;
                case HomeBannerDestinationType.Web:
                    var url = destinationPath.ToWebUrl();

                    // ユーザーアンケートのURLの場合はパラメータにMyIdを付与して開く
                    if (url.Contains(Credentials.UserQuestionnaireURL))
                    {
                        var myId = GetMyIdUseCase.GetMyId();
                        url = url + "?uid=" + myId;
                    }

                    CustomOpenURL.OpenURL(url);
                    break;
                case HomeBannerDestinationType.CreditShop:
                case HomeBannerDestinationType.BasicShop:
                    HomeViewControl.OnBasicShopSelected();
                    break;
                case HomeBannerDestinationType.AdventBattle:
                    Toast.MakeText($"遷移未実装 / {destinationType}").Show();
                    break;
                default:
                    throw new Exception($"invalid destinationType: {destinationType}");
            }
        }
    }
}
