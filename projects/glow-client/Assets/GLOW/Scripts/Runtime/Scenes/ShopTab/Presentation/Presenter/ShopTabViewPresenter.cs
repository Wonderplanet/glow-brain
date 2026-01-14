using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.PackShop.Presentation.Views;
using GLOW.Scenes.PassShop.Presentation.View;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.ShopTab.Presentation.View;
using UIKit;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ShopTab.Presentation.Presenter
{
    public class ShopTabViewPresenter : IShopTabViewDelegate
    {
        [Inject] ShopTabViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetShopProductNoticeUseCase GetShopProductNoticeUseCase { get; }
        [Inject] GetPackProductNoticeUseCase GetPackProductNoticeUseCase { get; }
        [Inject] GetPassProductNoticeUseCase GetPassProductNoticeUseCase { get; }
        [Inject] SaveNewShopProductUseCase SaveNewShopProductUseCase { get; }
        [Inject] InitializeNewShopProductIdUseCase InitializeNewShopProductIdUseCase { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] DailyRefreshWireFrame DailyRefreshWireFrame { get; }
        [Inject] DailyRefreshCheckUseCase DailyRefreshCheckUseCase { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        ShopContentTypes _currentContentType;

        void IShopTabViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ShopTabViewPresenter), nameof(IShopTabViewDelegate.OnViewDidLoad));

            InitializeNewShopProductIdUseCase.InitializeNewShopProductId();
            _currentContentType = ShopContentTypes.Shop;
        }


        void IShopTabViewDelegate.OnViewWillAppear()
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
            }

            // DidLoadで処理をすると初回でショップタブ以外に遷移したとき、
            // ショップタブ内のCellたちが走らせているImageLoaderでLoad完了前に画面が破棄されFailedが出る。
            // ので、ViewWillAppearで行う。
            if (ViewController.IsTransitionedByFooter)
            {
                // フッター遷移の場合
                var types = GetAvailableContentTypes();
                if (!types.IsEmpty())
                {
                    // 部分メンテ中でない先頭のタブを表示
                    SwitchContentByTransition(types.First(), MasterDataId.Empty);
                }
                else
                {
                    // 全てメンテ中の場合、ホーム画面へ戻る
                    ContentMaintenanceWireframe.ShowDialog(() => HomeFooterDelegate.BackToHome());
                }
                ViewController.IsTransitionedByFooter = false;
            }
        }

        void IShopTabViewDelegate.OnTabTapped(ShopContentTypes shopContentTypes)
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
                return;
            }

            if (CheckContentMaintenanceUseCase.IsInMaintenance(
                        ToContentMaintenanceTarget(shopContentTypes)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            SwitchContentByTabTap(shopContentTypes, MasterDataId.Empty);
        }

        void IShopTabViewDelegate.OnChangeShopContent(ShopContentTypes shopContentTypes, MasterDataId oprProductId)
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
                return;
            }
            SwitchContentByTransition(shopContentTypes, oprProductId);
        }

        // タブタップによる切り替え
        void SwitchContentByTabTap(ShopContentTypes contentType, MasterDataId oprProductId)
        {
            if(_currentContentType == contentType && ViewController.CurrentContentViewController != null)
                return;

            HomeFooterDelegate.UpdateBadgeStatus();

            // 前回画面の非表示処理を呼びたいのでDismissで対応
            ViewController.CurrentContentViewController?.Dismiss();

            // タブ切り替え先のViewを表示
            var controller = CreateContentViewController(contentType, oprProductId);
            ViewController.ShowCurrentContent(contentType, controller, true);

            ViewController.UpdateBadgeStatus(
                GetShopProductNoticeUseCase.GetShopProductNoticeAndSaveCache(),
                GetPassProductNoticeUseCase.GetPassProductNotice(),
                GetPackProductNoticeUseCase.GetPackProductNotice());
            _currentContentType = contentType;
        }

        // 他画面からの遷移による切り替え
        void SwitchContentByTransition(ShopContentTypes contentType, MasterDataId oprProductId)
        {
            if(_currentContentType == contentType && ViewController.CurrentContentViewController != null)
                return;

            HomeFooterDelegate.UpdateBadgeStatus();

            // 前回画面の非表示処理を呼ばれる事を避けるためRemoveFromParentしてUnloadViewで対応
            ViewController.CurrentContentViewController?.RemoveFromParent();
            ViewController.CurrentContentViewController?.UnloadView();

            // 画面遷移先のViewを表示
            var controller = CreateContentViewController(contentType, oprProductId);
            ViewController.ShowCurrentContent(contentType, controller, false);

            ViewController.UpdateBadgeStatus(
                GetShopProductNoticeUseCase.GetShopProductNoticeAndSaveCache(),
                GetPassProductNoticeUseCase.GetPassProductNotice(),
                GetPackProductNoticeUseCase.GetPackProductNotice());
            _currentContentType = contentType;
        }

        void IShopTabViewDelegate.ShowSpecificCommerce()
        {
            // 特定商取引ダイアログ表示
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IShopTabViewDelegate.ShowFundsSettlement()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement);
        }

        void IShopTabViewDelegate.UpdateShopTabBadge(bool isCheckOnlyAdvOrFree)
        {
            ViewController.ShowShopTabBadges(GetShopProductNoticeUseCase.GetShopProductNotice(isCheckOnlyAdvOrFree));
        }

        void IShopTabViewDelegate.UpdatePackTabBadge()
        {
            ViewController.ShowPackTabBadges(GetPackProductNoticeUseCase.GetPackProductNotice());
        }

        void IShopTabViewDelegate.UpdatePassTabBadge()
        {
            ViewController.ShowPassTabBadges(GetPassProductNoticeUseCase.GetPassProductNotice());
        }

        UIViewController CreateContentViewController(ShopContentTypes contentType, MasterDataId oprProductId)
        {
            switch (contentType)
            {
                case ShopContentTypes.Shop:
                    return ViewFactory.Create<ShopCollectionViewController>();
                case ShopContentTypes.Pack:
                    var packArg = new PackShopViewController.Argument(oprProductId);
                    return ViewFactory.Create<PackShopViewController, PackShopViewController.Argument>(packArg);
                case ShopContentTypes.Pass:
                    return ViewFactory.Create<PassShopListViewController>();
                default:
                    throw new ArgumentOutOfRangeException(nameof(contentType), contentType, null);
            }
        }

        List<ShopContentTypes> GetAvailableContentTypes()
        {
            var contentTypes = new[] { ShopContentTypes.Shop, ShopContentTypes.Pack, ShopContentTypes.Pass };
            return contentTypes
                .Where(type => !CheckContentMaintenanceUseCase.IsInMaintenance(ToContentMaintenanceTarget(type)))
                .ToList();
        }

        ContentMaintenanceTarget[] ToContentMaintenanceTarget(ShopContentTypes contentType)
        {
            return contentType switch
            {
                ShopContentTypes.Shop => ContentMaintenanceTarget.ShopItem,
                ShopContentTypes.Pack => ContentMaintenanceTarget.ShopPack,
                ShopContentTypes.Pass => ContentMaintenanceTarget.ShopPass,
                _ => new []{ ContentMaintenanceTarget.Empty },
            };
        }
    }
}
