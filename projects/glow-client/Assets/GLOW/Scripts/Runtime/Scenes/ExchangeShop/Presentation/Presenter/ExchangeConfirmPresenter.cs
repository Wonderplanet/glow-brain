using System;
using System.Collections.Generic;
using System.Threading;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.ExchangeShop.Presentation.Translator;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.ExchangeShop.Presentation.WireFrame;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.TradeShop.Presentation.View;
using GLOW.Scenes.UnitReceive.Presentation.View;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.Presenter
{
    public class ExchangeConfirmPresenter : IExchangeConfirmViewDelegate
    {
        [Inject] ExchangeShopConfirmViewController.Argument Argument { get; }
        [Inject] ExchangeShopConfirmViewController ViewController { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ApplyExchangeTradeUseCase ApplyExchangeTradeUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] GetExchangeableUseCase GetExchangeableUseCase { get; }
        [Inject] ExchangeConfirmWireFrame ExchangeConfirmWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        CancellationToken _cancellationToken;

        void IExchangeConfirmViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUpView(
                Argument.ExchangeConfirmViewModel,
                Argument.OnTradeCompleted,
                Argument.TradeIconViewModel);
        }

        void IExchangeConfirmViewDelegate.ShowItemDetail(PlayerResourceIconViewModel tradeIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(tradeIconViewModel, ViewController);
        }

        void IExchangeConfirmViewDelegate.OnTradeApply(Action onExchangeCompleted)
        {
            var exchangeType = GetExchangeableUseCase.GetExchangeable(
                Argument.MstExchangeLineupId,
                ViewController.SelectedTradeAmount);

            if (exchangeType != ExchangeRewardType.Exchangeable)
            {
                ShowExchangeError(exchangeType);
                return;
            }

            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async ct =>
            {
                try
                {
                    // 交換処理実行
                    var exchangeResult = await ApplyExchangeTradeUseCase.ApplyExchangeTrade(
                        ct,
                        Argument.MstExchangeId,
                        Argument.MstExchangeLineupId,
                        ViewController.SelectedTradeAmount);

                    ViewController.Dismiss();

                    // 交換した報酬の表示、演出
                    var viewModel = ExchangeResultViewModelTranslator.Translate(exchangeResult);
                    ShowExchangeRewardDirection(viewModel);

                    // 交換がで終わったら、商品一覧を更新
                    onExchangeCompleted?.Invoke();

                    // 画面表示の更新
                    UpdateScreenDisplay();
                }
                catch (ExchangeLineupMismatch)
                {
                    ExchangeConfirmWireFrame.BackToHomeTopLineupMismatch(
                        () => ViewController.Dismiss());
                }
                catch (ExchangeLineupTradeLimitExceeded)
                {
                    ExchangeConfirmWireFrame.BackToHomeTopLimitExceeded(
                        () => ViewController.Dismiss());
                }
                catch (ExchangeNotTradePeriod)
                {
                    ExchangeConfirmWireFrame.BackToHomeTopAfterTradePeriod(
                        () => ViewController.Dismiss());
                }
                catch (LackOfResourcesException)
                {
                    ExchangeConfirmWireFrame.BackToHomeTopShortageItem(
                        () => ViewController.Dismiss());
                }
            });
        }

        void UpdateScreenDisplay()
        {
            HomeHeaderDelegate.UpdateStatus();
            HomeFooterDelegate.UpdateBadgeStatus();
        }

        void ShowExchangeRewardDirection(ExchangeResultViewModel viewModel)
        {
            // 汎用報酬受取画面の表示
            ShowExchangeRewardView(viewModel.RewardModels);

            if (viewModel.IsArtworkReward())
            {
                ShowArtworkFragmentAcquisitionView(viewModel.ArtworkFragmentAcquisitionViewModel);
            }
            else if (viewModel.IsUnitReward())
            {
                ShowUnitReceiveView(viewModel.UnitReceiveViewModel);
            }
        }

        void ShowExchangeError(ExchangeRewardType type)
        {
            switch (type)
            {
                case ExchangeRewardType.ExchangeLimit:
                    CommonToastWireFrame.ShowScreenCenterToast("交換上限に達しています。");
                    break;
                case ExchangeRewardType.ShortageItem:
                    CommonToastWireFrame.ShowScreenCenterToast("アイテムが不足しています。");
                    break;
                case ExchangeRewardType.HasMaximumItem:
                    // 他の箇所に合わせて、ここは確認ダイアログにする
                    MessageViewUtil.ShowMessageWithClose(
                        "確認",
                        "所持上限を超えるため、\n交換できません。");
                    break;
            }
        }

        void ShowUnitReceiveView(UnitReceiveViewModel viewModel)
        {
            var argument = new UnitReceiveViewController.Argument(viewModel);
            var viewController = ViewFactory.Create<UnitReceiveViewController,
                UnitReceiveViewController.Argument>(argument);

            ViewController.PresentModally(viewController);
        }

        void ShowArtworkFragmentAcquisitionView(
            ArtworkFragmentAcquisitionViewModel viewModel,
            Action onViewClosed = null)
        {
            var argument = new ArtworkFragmentAcquisitionViewController.Argument(
                viewModel,
                onViewClosed);

            var viewController = ViewFactory.Create<ArtworkFragmentAcquisitionViewController,
                ArtworkFragmentAcquisitionViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }

        void ShowExchangeRewardView(IReadOnlyList<CommonReceiveResourceViewModel> models)
        {
            CommonReceiveWireFrame.Show(models);
        }
    }
}
