using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaRatio.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaLineupDialog.Presentation.Views;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using GLOW.Scenes.GachaWireFrame.Presentation.ValueObject;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaWireFrame.Presentation.Presenters
{
    public enum UnitDetailOpenType
    {
        Ratio,
        LineUp,
        Other
    }

    public class GachaWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] UICanvas Canvas { get; }

        GachaContentViewController _gachaContentViewController;
        IGachaListViewController _gachaListViewController;
        GachaRatioDialogViewController _gachaRatioDialogViewController;
        GachaLineupDialogViewController _gachaLineupViewController;
        UnitDetailViewContentType _currentUnitDetailViewContentType = UnitDetailViewContentType.Empty;

        public Action OnGachaRatioCloseAction { get; set; }
        public Action OnGachaLineUpCloseAction { get; set; }

        UIViewController RootViewController => Canvas.RootViewController;

        UnitDetailOpenType UnitDetailOpenType
        {
            get
            {
                if (_gachaRatioDialogViewController != null)
                {
                    return UnitDetailOpenType.Ratio;
                }
                else if (_gachaLineupViewController != null)
                {
                    return UnitDetailOpenType.LineUp;
                }
                else
                {
                    return UnitDetailOpenType.Other;
                }
            }
        }

        public void RegisterGachaContentViewController(GachaContentViewController gachaContentViewController)
        {
            _gachaContentViewController = gachaContentViewController;
        }

        public void UnregisterGachaContentViewController()
        {
            _gachaContentViewController = null;
        }

        public void RegisterGachaListViewController(IGachaListViewController gachaListViewController)
        {
            _gachaListViewController = gachaListViewController;
        }

        public void UnregisterGachaListViewController()
        {
            _gachaListViewController = null;
        }

        public void ShowGachaContentView(MasterDataId gachaId)
        {
            _gachaContentViewController = ViewFactory.Create<GachaContentViewController, MasterDataId>(gachaId);
            _gachaListViewController?.SetCurrentGachaContentViewController(_gachaContentViewController);
        }

        // ガシャ詳細の表示
        public void ShowGachaDetailView(
            MasterDataId gachaId,
            GachaDetailDialogViewModel viewModel,
            UIViewController parentViewController)
        {
            // ガシャ詳細の表示
            var argument = new GachaDetailDialogViewController.Argument(viewModel);
            var controller = CreateGachaDetailViewController(argument);
            parentViewController.PresentModally(controller);
        }

        public void ShowGachaRatioDialogView(
            MasterDataId gachaId,
            GachaRatioDialogViewModel viewModel,
            UIViewController parentViewController)
        {
            var argument = new GachaRatioDialogViewController.Argument(gachaId, viewModel);
            _gachaRatioDialogViewController = CreateGachaRatioDialogViewController(argument);
            parentViewController.PresentModally(_gachaRatioDialogViewController);
        }

        public void ShowGachaLineUpDialogView(
            MasterDataId gachaId,
            GachaLineupDialogViewModel viewModel,
            UIViewController parentViewController)
        {
            var argument = new GachaLineupDialogViewController.Argument(gachaId, viewModel);
            _gachaLineupViewController = CreateGachaLineUpDialogViewController(argument);
            parentViewController.PresentModally(_gachaLineupViewController);
        }

        public void OnCloseGachaRatioDialogViewAndInvokeAction()
        {
            if (_gachaRatioDialogViewController == null) return;

            _gachaRatioDialogViewController?.Dismiss();
            _gachaRatioDialogViewController = null;

            OnGachaRatioCloseAction?.Invoke();
            OnGachaRatioCloseAction = null;
        }

        public void OnCloseGachaLineUpDialogViewAndInvokeAction()
        {
            if (_gachaLineupViewController == null) return;

            _gachaLineupViewController?.Dismiss();
            _gachaLineupViewController = null;

            OnGachaLineUpCloseAction?.Invoke();
            OnGachaLineUpCloseAction = null;
        }

        public void OnClickIconDetail(PlayerResourceModel resourceModel, UIViewController parentViewController)
        {
            if (resourceModel.Type == ResourceType.Item)
            {
                if (resourceModel.IsEmpty()) return;

                OnItemDetailView(resourceModel, parentViewController);
            }
            else if (resourceModel.Type == ResourceType.Unit)
            {
                OnUnitDetailView(resourceModel.Id, parentViewController);
            }
        }

        void OnCloseGachaRatioDialogView()
        {
            if (_gachaRatioDialogViewController == null) return;

            _gachaRatioDialogViewController?.Dismiss();
            _gachaRatioDialogViewController = null;
        }

        void OnCloseGachaLineupDialogView()
        {
            if (_gachaLineupViewController == null) return;

            _gachaLineupViewController?.Dismiss();
            _gachaLineupViewController = null;
        }

        void OnItemDetailView(PlayerResourceModel playerResourceModel, UIViewController parentViewController)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                playerResourceModel.Type,
                playerResourceModel.Id,
                playerResourceModel.Amount,
                parentViewController);
        }

        void OnUnitDetailView(MasterDataId unitId, UIViewController parentViewController)
        {
            if(UnitDetailOpenType == UnitDetailOpenType.Ratio)
            {
                ShowUnitDetailViewFromRatio(unitId, parentViewController);
            }
            else if (UnitDetailOpenType == UnitDetailOpenType.LineUp)
            {
                ShowUnitDetailViewFromLineUp(unitId, parentViewController);
            }
        }

        void ShowUnitDetailViewFromRatio(MasterDataId unitId, UIViewController parentViewController)
        {
            var vcArgument = _gachaRatioDialogViewController.Args;
            var normalizedPos = _gachaRatioDialogViewController.NormalizedPos;
            var tabType = _gachaRatioDialogViewController.CurrentTab;

            var argument = new UnitDetailModalViewController.Argument(unitId, MaxStatusFlag.True);
            var controller = ViewFactory.Create<
                UnitDetailModalViewController,
                UnitDetailModalViewController.Argument>(argument);

            var currentContentType = HomeViewController.ViewContextController.CurrentContentType;
            _currentUnitDetailViewContentType = new UnitDetailViewContentType(currentContentType);

            controller.OnClose = () =>
            {
                // 閉じる際に画面を移動していた場合は再表示しない
                var currentContentTypeAfterClose = HomeViewController.ViewContextController.CurrentContentType;

                var isSameContentType = _currentUnitDetailViewContentType.IsSameContentType(currentContentTypeAfterClose);
                _currentUnitDetailViewContentType = UnitDetailViewContentType.Empty;

                if (!isSameContentType) return;

                _gachaRatioDialogViewController = CreateGachaRatioDialogViewController(vcArgument);
                
                parentViewController?.PresentModally(_gachaRatioDialogViewController, false);
                
                _gachaRatioDialogViewController.GachaRatioPageUpdate(tabType);
                _gachaRatioDialogViewController.MoveScrollToTargetPos(normalizedPos);
            };
            OnCloseGachaRatioDialogView();

            RootViewController.PresentModally(controller);
        }

        void ShowUnitDetailViewFromLineUp(MasterDataId unitId, UIViewController parentViewController)
        {
            var vcArgument = _gachaLineupViewController.Args;
            var normalizedPos = _gachaLineupViewController.NormalizedPos;
            var tabType = _gachaLineupViewController.CurrentTab;

            var argument = new UnitDetailModalViewController.Argument(unitId, MaxStatusFlag.True);
            var controller = ViewFactory.Create<
                UnitDetailModalViewController,
                UnitDetailModalViewController.Argument>(argument);

            var currentContentType = HomeViewController.ViewContextController.CurrentContentType;
            _currentUnitDetailViewContentType = new UnitDetailViewContentType(currentContentType);

            controller.OnClose = () =>
            {
                // 閉じる際に画面を移動していた場合は再表示しない
                var currentContentTypeAfterClose = HomeViewController.ViewContextController.CurrentContentType;

                var isSameContentType = _currentUnitDetailViewContentType.IsSameContentType(currentContentTypeAfterClose);
                _currentUnitDetailViewContentType = UnitDetailViewContentType.Empty;

                if (!isSameContentType) return;

                _gachaLineupViewController = CreateGachaLineUpDialogViewController(vcArgument);

                parentViewController?.PresentModally(_gachaLineupViewController, false);

                _gachaLineupViewController.GachaRatioPageUpdate(tabType);
                _gachaLineupViewController.MoveScrollToTargetPos(normalizedPos);
            };
            OnCloseGachaLineupDialogView();

            RootViewController.PresentModally(controller);
        }

        GachaDetailDialogViewController CreateGachaDetailViewController(
            GachaDetailDialogViewController.Argument argument)
        {
            return ViewFactory.Create<
                GachaDetailDialogViewController,
                GachaDetailDialogViewController.Argument>(argument);
        }

        GachaRatioDialogViewController CreateGachaRatioDialogViewController(
            GachaRatioDialogViewController.Argument argument)
        {
            return ViewFactory.Create<
                GachaRatioDialogViewController,
                GachaRatioDialogViewController.Argument>(argument);
        }

        GachaLineupDialogViewController CreateGachaLineUpDialogViewController(
            GachaLineupDialogViewController.Argument argument)
        {
            return ViewFactory.Create<
                GachaLineupDialogViewController,
                GachaLineupDialogViewController.Argument>(argument);
        }
    }
}
