using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.PageContent;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.PackShop.Presentation.ViewModels.StageClearPackPageContent;
using GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;
using Object = UnityEngine.Object;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopViewController :
        HomeBaseViewController<PackShopView>,
        IPackShopViewController,
        IPageContentViewControllerDataSource,
        IPageContentViewControllerPageControlDataSource
    {
        public record Argument(MasterDataId TargetId);
        [Inject] IPackShopViewDelegate ViewDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }

        PageContentViewController _pageContentViewController;
        PackShopProductListViewModel _products;

        List<Coroutine> _countDownCoroutines = new ();
        List<UIViewController> _stageClearCells = new ();
        List<PackShopProductListCell> _packCells = new ();

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void SetupProductList(PackShopProductListViewModel viewModel)
        {
            foreach (var coroutine in _countDownCoroutines)
            {
                if(null == coroutine) continue;
                ActualView.StopCoroutine(coroutine);
            }
            _countDownCoroutines.Clear();

            _products = viewModel;
            SetupStageClearPacks(viewModel.StageClearPacks);
            SetupNormalPacks();

            ActualView.SetDailyPackRemainingTime(viewModel.RemainingDailyPackTime);
        }

        public void PlayCellAppearanceAnimation()
        {
            // セルが無い場合はアニメーションしない
            if(_packCells.Count <= 0) return;

            ActualView.PlayCellAppearanceAnimation();
        }

        public void FocusTarget(PackShopProductListViewModel viewModel, MasterDataId targetId)
        {
            // ターゲットが無ければそのまま抜ける
            if(targetId.IsEmpty()) return;

            var isTarget = viewModel.StageClearPacks.Any(model => model.OprProductId == targetId)
                           || viewModel.NormalPacks.Any(model => model.OprProductId == targetId);

            //TODO:指定されているターゲットが存在しなければ戻る
        }

        UIViewController IPackShopViewController.UIViewController
        {
            get { return this; }
        }

        void IPackShopViewController.ShowProductInfo(MasterDataId mstPackId)
        {
            ViewDelegate.OnShowInfoSelected(mstPackId);
        }

        void IPackShopViewController.ShowPackShopGacha(MasterDataId ticketId, MasterDataId mstPackId, float targetPosY)
        {
            // ガシャ一覧ダイアログを閉じた際に使用
            var argument = new PackShopGachaViewController.Argument(ticketId, this, mstPackId);
            var controller = ViewFactory.Create<PackShopGachaViewController, PackShopGachaViewController.Argument>(argument);
            PresentModally(controller);
            controller.MoveScrollToTargetPos(targetPosY);
        }

        void SetupStageClearPacks(IReadOnlyList<PackShopProductViewModel> stageClearPacks)
        {
            _stageClearCells.Clear();
            if (_pageContentViewController == null)
            {
                _pageContentViewController = new PageContentViewController()
                {
                    View = ActualView.PageContentView,
                    DataSource = this,
                    PageControlDataSource = this,
                };
                _pageContentViewController.ViewDidLoad();
                this.AddChild(_pageContentViewController);
            }
            else
            {
                _pageContentViewController.ResetViews();
            }

            _stageClearCells = stageClearPacks
                .Select(CreateStageClearPackCell)
                .ToList();
            _pageContentViewController.ActualView.Hidden = _stageClearCells.Count <= 0;
            if (!_pageContentViewController.ActualView.Hidden)
            {
                _pageContentViewController.SetViewControllers(new List<UIViewController>() { _stageClearCells[0] },
                    PageContentViewController.NavigationDirection.Forward,
                    true);
            }
        }

        void SetupNormalPacks()
        {
            foreach (var cell in _packCells)
            {
                Object.Destroy(cell.gameObject);
            }
            _packCells.Clear();

            ActualView.SetNormalPackSectionHeaderVisible(_products.NormalPacks.Count > 0);
            ActualView.SetNormalPackContentAreaVisible(_products.NormalPacks.Count > 0);
            foreach (var product in _products.NormalPacks)
            {
                var cell = CreateNormalPackCell(product);
                _packCells.Add(cell);
            }

            ActualView.SetDailyPackContentAreaVisible(_products.DailyPacks.Count > 0);
            foreach(var product in _products.DailyPacks)
            {
                var cell = CreateDailyPackCell(product);
                _packCells.Add(cell);
            }
        }

        PackShopProductListCell CreateNormalPackCell(PackShopProductViewModel viewModel)
        {
            var cell = ActualView.InstantiateNormalPackCell(viewModel,
                ViewDelegate.OnBuyProductSelected,
                ViewDelegate.OnShowInfoSelected);
            SetRemainTimeLimit(cell, viewModel);
            return cell;
        }

        PackShopProductListCell CreateDailyPackCell(PackShopProductViewModel viewModel)
        {
            var cell = ActualView.InstantiateDailyPackCell(viewModel,
                ViewDelegate.OnBuyProductSelected,
                ViewDelegate.OnShowInfoSelected);
            SetRemainTimeLimit(cell, viewModel);
            return cell;
        }

        UIViewController CreateStageClearPackCell(PackShopProductViewModel viewModel)
        {
            var args = new StageClearPackPageContentViewModel(viewModel,
                ViewDelegate.OnBuyProductSelected,
                ViewDelegate.OnShowInfoSelected);
            var viewController = ViewFactory.Create<StageClearPackPageContentViewController>();
            viewController.SetViewModel(args);
            SetRemainTimeLimit(viewController.ActualView.Cell, viewModel);
            return viewController;
        }

        void SetRemainTimeLimit(PackShopProductListCell cell, PackShopProductViewModel viewModel)
        {
            if (viewModel.EndDateTime.IsInfinity())
            {
                cell.SetEndTimeInfinity();
                return;
            }

            var coroutine = ActualView.StartCoroutine(UpdateRemainTimeLimit(cell, viewModel));
            _countDownCoroutines.Add(coroutine);
        }

        IEnumerator UpdateRemainTimeLimit(PackShopProductListCell cell, PackShopProductViewModel viewModel)
        {
            var countDown = ViewDelegate.GetRemainCountDown(viewModel.EndDateTime);
            while (countDown != TimeSpan.Zero)
            {
                var waitTime = countDown.Milliseconds * 0.001f;
                cell.UpdateEndTime(countDown);
                yield return new WaitForSeconds(waitTime);
                countDown = ViewDelegate.GetRemainCountDown(viewModel.EndDateTime);
            }

            cell.UpdateEndTime(countDown);
        }


        UIViewController IPageContentViewControllerDataSource.ViewControllerBefore(
            PageContentViewController pageViewController,
            UIViewController viewController)
        {
            var index = _stageClearCells.IndexOf(viewController);
            if(index > 0) return _stageClearCells[index - 1];
            return _stageClearCells.Last();
        }

        UIViewController IPageContentViewControllerDataSource.ViewControllerAfter(
            PageContentViewController pageViewController,
            UIViewController viewController)
        {
            int index = _stageClearCells.IndexOf(viewController);
            if(index < _stageClearCells.Count - 1) return _stageClearCells[index + 1];
            return _stageClearCells.First();
        }

        int IPageContentViewControllerPageControlDataSource.PresentationCount(PageContentViewController pageViewController)
        {
            return _stageClearCells.Count;
        }

        int IPageContentViewControllerPageControlDataSource.PresentationIndex(PageContentViewController pageViewController)
        {
            return 0;
        }
    }
}
