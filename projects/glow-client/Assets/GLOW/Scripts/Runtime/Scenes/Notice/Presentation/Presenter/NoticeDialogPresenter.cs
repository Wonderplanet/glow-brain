using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Scenes.Notice.Domain.UseCase;
using GLOW.Scenes.Notice.Presentation.Navigation;
using GLOW.Scenes.Notice.Presentation.View;
using Zenject;

namespace GLOW.Scenes.Notice.Presentation.Presenter
{
    /// <summary>
    /// 12-3_ノーティス
    /// </summary>
    public class NoticeDialogPresenter : INoticeDialogViewDelegate
    {
        [Inject] NoticeDialogViewController ViewController { get; }
        [Inject] NoticeDialogViewController.Argument Argument { get; }
        [Inject] INoticeNavigator NoticeNavigator { get; }
        [Inject] SaveNoticeDisplayUseCase SaveNoticeDisplayUseCase { get; }

        public void OnViewWillAppear()
        {
            SaveNoticeDisplayUseCase.SaveInGameNoticeDisplay(
                Argument.ViewModel.NoticeId,
                Argument.ViewModel.DisplayFrequencyType);
            ViewController.SetViewModel(Argument.ViewModel);
        }

        public void OnCloseSelected()
        {
            ViewController.Dismiss();
            ViewController.OnCloseCompletion?.Invoke();
        }

        public void OnTransitSelected()
        {
            ViewController.Dismiss();
            ViewController.OnTransitCompletion?.Invoke();
            TransitTo();
        }

        void TransitTo()
        {
            var destinationType = NoticeDestinationType.TryToEnum(Argument.ViewModel.DestinationType.Value);
            if (destinationType == NoticeDestinationTypeEnum.Empty)
                return;

            if (destinationType == NoticeDestinationTypeEnum.Web)
                NoticeNavigator.ShowUrl(Argument.ViewModel.DestinationScene);

            var destinationPath = Argument.ViewModel.DestinationScene.TryToEnum();
            switch (destinationPath)
            {
                case DestinationSceneEnum.Shop:
                    NoticeNavigator.ShowBasicShopTopView();
                    break;
                case DestinationSceneEnum.Pack:
                    NoticeNavigator.ShowPackShopTopView();
                    break;
                case DestinationSceneEnum.Gacha:
                    NoticeNavigator.ShowGachaView(Argument.ViewModel.NoticeDestinationPathDetail);
                    break;
                case DestinationSceneEnum.Event:
                    NoticeNavigator.ShowContentTopView();
                    break;
                case DestinationSceneEnum.Pass:
                    NoticeNavigator.ShowPassShopTopView();
                    break;
                case DestinationSceneEnum.Pvp:
                    NoticeNavigator.ShowPvpTopView();
                    break;
                case DestinationSceneEnum.Exchange:
                    NoticeNavigator.ShowExchangeShopView(Argument.ViewModel.NoticeDestinationPathDetail);
                    break;
                default:
                    break;
            }
        }
    }
}
