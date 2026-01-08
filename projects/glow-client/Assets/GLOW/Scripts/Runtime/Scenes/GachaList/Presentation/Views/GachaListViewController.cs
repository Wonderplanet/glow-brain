using System;
using System.Collections.Generic;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-5_ガシャ一覧画面
    /// </summary>
    public interface IGachaListViewController
    {
        UIView GetActualView { get; }
        UIViewController GetViewController { get; }
        void PresentModally(UIViewController controller, bool animated = true, Action completion = null);
        void SetCurrentGachaContentViewController(GachaContentViewController viewController);
        void UpdateCurrentGachaContentViewController();
        void DisableScroll();
        void UpdateView();
    }

    public interface IGachaContentViewFactory
    {
        GachaContentViewController CreateGachaContentViewController(MasterDataId gachaId);
    }

    public class GachaListViewController :
        HomeBaseViewController<GachaListView>,
        IGachaListViewController,
        IGachaContentViewFactory
    {
        [Inject] IGachaListViewDelegate GachaListViewDelegate { get; }
        [Inject] IFestivalGachaBannerImageLoader FestivalBannerImageLoader { get; }

        MasterDataId _currentTappedGachaId = MasterDataId.Empty;
        GachaContentViewController _currentGachaContentViewController;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            GachaListViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            UpdateView();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            
            _currentTappedGachaId = MasterDataId.Empty;
            GachaListViewDelegate.OnViewDidUnLoad();
        }

        UIView IGachaListViewController.GetActualView => ActualView;

        UIViewController IGachaListViewController.GetViewController => this;

        void IGachaListViewController.PresentModally(UIViewController controller, bool animated, Action completion)
        {
            base.PresentModally(controller, animated, completion);
        }

        void IGachaListViewController.SetCurrentGachaContentViewController(GachaContentViewController viewController)
        {
            _currentGachaContentViewController = viewController;
        }

        void IGachaListViewController.UpdateCurrentGachaContentViewController()
        {
            _currentGachaContentViewController?.UpdateView();
        }

        // ViewControllerのInstallerでViewFactoryが区切られているので、仕方なくここからCreateする
        GachaContentViewController IGachaContentViewFactory.CreateGachaContentViewController(MasterDataId gachaId)
        {
            return GachaListViewDelegate.CreateGachaContentViewController(gachaId);
        }

        public void UpdateView()
        {
            ActualView.DestroyGachaBannerComponents();

            var model = GachaListViewDelegate.UpdateListView();
            SetGachaListViewModel(model);

            ActualView.PlayCellAppearanceAnimation();

            if (!_currentTappedGachaId.IsEmpty())
            {
                ActualView.ScrollByGachaId(_currentTappedGachaId);
                _currentTappedGachaId = MasterDataId.Empty;
            }
        }

        void SetGachaListViewModel(GachaListViewModel model)
        {
            SetTutorialGachaBannerViewModel(model.TutorialGachaBannerViewModel);
            SetFesGachaBannerViewModel(model.FestivalBannerViewModels);
            
            SetGachaBannerViewModel(GachaType.Pickup, model.PickupBannerViewModels);
            SetGachaBannerViewModel(GachaType.Free, model.FreeBannerViewModels);
            SetGachaBannerViewModel(GachaType.Ticket, model.TicketBannerViewModels);
            SetGachaBannerViewModel(GachaType.PaidOnly, model.PaidOnlyBannerViewModels);

            // メダルガシャ表示
            SetMedalGachaBannerViewModel(model.MedalGachaBannerViewModels);

            SetAlwaysPresentGachas(
                model.PremiumGachaViewModel,
                model.HeldAdSkipPassInfoViewModel);
            
        }

        void SetGachaBannerViewModel(GachaType gachaType, IReadOnlyList<GachaBannerViewModel> models)
        {
            if (models.Count <= 0) return;

            ActualView.SetGachaBannerViewModel(gachaType, models, BannerTapped, ShowGachaRatioDialog);

        }

        void SetFesGachaBannerViewModel(IReadOnlyList<FestivalGachaBannerViewModel> viewModels)
        {
            if (viewModels.Count <= 0) return;

            ActualView.CreateFestivalGachaBandComponent();
                
            foreach (var viewModel in viewModels)
            {
                var component = ActualView.CreateFestivalGachaBannerComponent(viewModel, BannerTapped, ShowGachaRatioDialog);
                LoadSingleBannerImageAsync(viewModel, component).Forget();
            }
        }

        async UniTaskVoid LoadSingleBannerImageAsync(
            FestivalGachaBannerViewModel viewModel,
            FestivalGachaBannerComponent component)
        {
            var bannerImage = await FestivalBannerImageLoader.LoadBannerImage(
                viewModel.FestivalGachaBannerAssetPath,
                ActualView.GetCancellationTokenOnDestroy());

            if (bannerImage != null)
            {
                component.SetGachaBannerImage(bannerImage);
            }
        }

        void SetMedalGachaBannerViewModel(IReadOnlyList<MedalGachaBannerViewModel> viewModels)
        {
            if (viewModels.Count <= 0) return;

            ActualView.SetMedalGachaBannerViewModel(viewModels, ShowGachaConfirmDialog, ShowGachaLineUpDialog);
        }

        void SetAlwaysPresentGachas(
            PremiumGachaViewModel premiumGacha,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            ActualView.SetAlwaysPresentGachaViewModels(
                premiumGacha, 
                heldAdSkipPassInfoViewModel, 
                ShowGachaConfirmDialog, 
                ShowGachaRatioDialog,
                ShowGachaDetailDialog);
        }

        void ShowGachaConfirmDialog(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            // ガシャ確認ダイアログの表示
            if (GachaListViewDelegate.ShowGachaConfirmDialogView(gachaId, gachaDrawType))
            {
                // 表示した時のみタップしたガシャIDを保存
                _currentTappedGachaId = gachaId;
            }
        }

        void ShowGachaRatioDialog(MasterDataId gachaId)
        {
            // ガシャ提供割合ダイアログの表示
            _currentTappedGachaId = gachaId;
            GachaListViewDelegate.ShowGachaRatioDialogView(gachaId);
        }

        void ShowGachaLineUpDialog(MasterDataId gachaId)
        {
            // ガシャラインナップダイアログの表示
            _currentTappedGachaId = gachaId;
            GachaListViewDelegate.ShowGachaLineUpDialogView(gachaId);
        }
        
        void ShowGachaDetailDialog(MasterDataId gachaId)
        {
            // ガシャ詳細ダイアログの表示
            _currentTappedGachaId = gachaId;
            GachaListViewDelegate.ShowGachaDetailDialogView(gachaId);
        }

        void BannerTapped(MasterDataId gachaId)
        {
            _currentTappedGachaId = gachaId;
            GachaListViewDelegate.OnBannerTapped(gachaId);
        }

        void SetTutorialGachaBannerViewModel(TutorialGachaBannerViewModel model)
        {
            if (model.IsEmpty()) return;

            // チュートリアルガシャは確認なしにボタンから直接引く
            ActualView.CreateTutorialGachaBannerViewModel(
                model,
                TutorialDrawButtonTapped,
                ShowGachaLineUpDialog);
        }

        void TutorialDrawButtonTapped(MasterDataId gachaId)
        {
            _currentTappedGachaId = gachaId;
            GachaListViewDelegate.OnTutorialGachaDrawButtonTapped();
        }

        void IGachaListViewController.DisableScroll()
        {
           ActualView.StopScroll();
        }

        [UIAction]
        public void OnGachaHistoryButtonTapped()
        {
            GachaListViewDelegate.OnGachaHistoryButtonTapped();
        }
    }
}
