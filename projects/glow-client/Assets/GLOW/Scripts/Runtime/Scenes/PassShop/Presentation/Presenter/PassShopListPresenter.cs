using System;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.PassShop.Domain.Enum;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PassShop.Presentation.View;
using GLOW.Scenes.PassShopProductDetail.Presentation.View;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Domain.ValueObjects;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopTab.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PassShop.Presentation.Presenter
{
    public class PassShopListPresenter : IPassShopListViewDelegate
    {
        [Inject] PassShopListViewController ViewController { get; }
        [Inject] ShowPassShopProductListUseCase ShowPassShopProductListUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] CheckPassPurchasableUseCase CheckPassPurchasableUseCase { get; }
        [Inject] DailyRefreshWireFrame DailyRefreshWireFrame { get; }
        [Inject] PurchasePassUseCase PurchasePassUseCase { get; }
        [Inject] IInAppPurchaseExecuteDelegate InAppPurchaseExecuteDelegate { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IShopConfirmViewUtil ShopConfirmViewUtil { get; }
        [Inject] IPassExceptionMessageWireframe PassExceptionMessageWireframe { get; }
        [Inject] IShopTabViewDelegate ShopTabViewDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }

        CancellationToken PassShopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IPassShopListViewDelegate.OnViewWillAppear()
        {
            InitializePassCellList();
        }

        void IPassShopListViewDelegate.OnInfoButtonSelected(MasterDataId mstShopPassId)
        {
            var argument = new PassShopProductDetailViewController.Argument(mstShopPassId);
            var controller = ViewFactory.Create<PassShopProductDetailViewController,
                PassShopProductDetailViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void IPassShopListViewDelegate.OnPurchaseButtonSelected(
            MasterDataId mstShopPassId,
            Action<RemainingTimeSpan> setUpCellRemainingTimeAction)
        {
            var cannotPurchasedReason = CheckPassPurchasableUseCase.CheckPassPurchaseStatus(mstShopPassId);
            switch (cannotPurchasedReason)
            {
                case PassUnpurchasableReason.IsDateChanged:
                    // 日付が変わっている場合は、ダイアログを出して画面を更新する
                    DailyRefreshWireFrame.ShowTitleBackView();
                    return;
                case PassUnpurchasableReason.IsInvalidPass:
                    // 購入しようとしたパスが期限切れの場合は、ダイアログを出して画面を更新する
                    PassExceptionMessageWireframe.ShowExpiredPassPurchaseErrorMessage(OnUpdatePassCellList);
                    return;
                case PassUnpurchasableReason.Purchased:
                    // 既に購入済みの場合は、ダイアログを出す
                    PassExceptionMessageWireframe.ShowAlreadyPurchasedPassMessage(OnUpdatePassCellList);
                    return;
                default:
                    // 購入可能なパスの場合は、購入処理を実行する
                    break;
            }

            ShopConfirmViewUtil.ShowPassConfirmView(ViewController,
                mstShopPassId,
                DisplayCostType.Cash, // PassはCashOnly
                () =>
                {
                    PurchasePass(mstShopPassId, setUpCellRemainingTimeAction);
                });
        }

        void IPassShopListViewDelegate.OnItemIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                viewModel.ResourceType,
                viewModel.Id,
                viewModel.Amount,
                ViewController);
        }

        void PurchasePass(
            MasterDataId mstShopPassId,
            Action<RemainingTimeSpan> setUpCellRemainingTimeAction)
        {
            InAppPurchaseExecuteDelegate.ExecutePurchase(
                PassShopCancellationToken,
                async ct =>
                {
                    try
                    {
                        var result = await PurchasePassUseCase.PurchasePass(ct, mstShopPassId);

                        MessageViewUtil.ShowMessageWithClose(
                            "購入完了",
                            ZString.Format("{0}の購入が完了しました", result.PassProductName.ToString()));

                        setUpCellRemainingTimeAction(result.PassEffectValidRemainingTime);
                        // バッジ更新
                        ShopTabViewDelegate.UpdatePassTabBadge();
                        HomeFooterDelegate.UpdateBadgeStatus();
                    }
                    catch (MstNotFoundException)
                    {
                        // 購入しようとしたパスが期限切れの場合は、ダイアログを出して画面を更新する
                        PassExceptionMessageWireframe.ShowExpiredPassPurchaseErrorMessage(OnUpdatePassCellList);
                    }
                });
        }

        void InitializePassCellList()
        {
            CreatePassCellList();
        }

        void OnUpdatePassCellList()
        {
            ViewController.RemoveAllPassShopProductListCells();
            CreatePassCellList();
            // バッジ更新
            ShopTabViewDelegate.UpdatePassTabBadge();
            HomeFooterDelegate.UpdateBadgeStatus();
        }

        void CreatePassCellList()
        {
            var passModels = ShowPassShopProductListUseCase.GetPassShopProductList();
            var viewModels = passModels
                .Select(PassShopProductViewModelTranslator.ToProductViewModel)
                .ToList();

            ViewController.SetupPassProductList(viewModels);
            // CollectionViewの初期化後にアニメーションを実行するためにずらす
            DoAsync.Invoke(ViewController.ActualView.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
                ViewController.PlayCellAppearanceAnimation();
            });
        }
    }
}
