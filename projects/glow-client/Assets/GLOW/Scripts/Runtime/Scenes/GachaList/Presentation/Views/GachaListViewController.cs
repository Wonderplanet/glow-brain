using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.Views.GachaListBannerControl;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using Wonderplanet.UIHaptics.Presentation;
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
        void InitializeView(GachaListViewModel viewModel);
        void UpdateView(GachaListViewModel viewModel);
        void PresentModally(UIViewController controller, bool animated = true, Action completion = null);
    }

    public interface IGachaContentViewController
    {
        void UpdateContentView(
            GachaContentAssetViewModel gachaContentAssetViewModel,
            GachaContentViewModel viewModel,
            bool showTransitAnimation);
    }

    public interface IGachaContentViewFactory
    {
        void ChangeTargetGachaContent(MasterDataId oprGachaId);
    }


    public class GachaListViewController :
        HomeBaseViewController<GachaListView>,
        IGachaListViewController,
        IGachaContentViewController,
        IGachaContentViewFactory
    {
        [Inject] IGachaListViewDelegate GachaListViewDelegate { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] IGachaContentAssetContainer GachaContentAssetContainer { get; }

        MasterDataId _currentTappedOprGachaId = MasterDataId.Empty;
        GachaListViewModel _gachaListViewModel;
        GachaContentViewController _currentGachaContentViewController;


        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            GachaListViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            GachaListViewDelegate.OnViewWillAppear();
            //homeMainViewが非表示、かつ一度アプリが非アクティブになると触覚FBが止まるので再開させる
            HapticsPresenter.SyncRestartEngine();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            _currentTappedOprGachaId = MasterDataId.Empty;
            GachaListViewDelegate.OnViewDidUnLoad();
        }

        #region IGachaContentViewFactory
        void IGachaContentViewFactory.ChangeTargetGachaContent(MasterDataId oprGachaId)
        {
            GachaListViewDelegate.UpdateView(oprGachaId);
        }
        #endregion


        # region IGachaListViewController
        UIView IGachaListViewController.GetActualView => ActualView;
        UIViewController IGachaListViewController.GetViewController => this;

        void IGachaListViewController.PresentModally(UIViewController controller, bool animated, Action completion)
        {
            base.PresentModally(controller, animated, completion);
        }

        void IGachaListViewController.UpdateView(GachaListViewModel viewModel)
        {
            _currentTappedOprGachaId = viewModel.InitialShowOprGachaId;
            _gachaListViewModel = viewModel;

            // フッターバナー更新
            BannerControlDelegate.UpdateViewModel(viewModel.InitialShowOprGachaId, viewModel.GetGachaFooterBannerViewModels());

            // 表示ガシャコンテンツの更新
            UpdateContentView(viewModel.InitialShowOprGachaId);
        }
        # endregion

        void IGachaListViewController.InitializeView(GachaListViewModel viewModel)
        {
            InitializeView(viewModel);
        }

        void InitializeView(GachaListViewModel viewModel)
        {
            _currentTappedOprGachaId = viewModel.InitialShowOprGachaId;
            _gachaListViewModel = viewModel;

            // フッターバナーの初期化
            InitializeBannerControl(viewModel.InitialShowOprGachaId, viewModel.GetGachaFooterBannerViewModels());

            // 初期表示ガシャコンテンツの更新
            InitielizeContentView(viewModel.InitialShowOprGachaId);

            // カルーセルボタン表示設定
            ActualView.SetCarouselVisibility(1 < viewModel.GetListElementViewModels().Count);
        }

        # region フッターバナー周り
        GachaListBannerControl.GachaListBannerControl _bannerControl;
        IGachaListBannerControlInitializer BannerControlInitializer => _bannerControl;
        IGachaListBannerControlDelegate BannerControlDelegate => _bannerControl;

        void InitializeBannerControl(
            MasterDataId initialShowOprGachaId,
            IReadOnlyList<GachaFooterBannerViewModel> gachaFooterBannerViewModels)
        {
            _bannerControl = new GachaListBannerControl.GachaListBannerControl(
                ActualView.FooterCarouselView,
                initialShowOprGachaId,
                gachaFooterBannerViewModels,
                OnFooterBannerSelect,
                ActualView.MaxDistanceMargin,
                ActualView.CellSizeMargin
                );
            BannerControlInitializer.InitializeView(HapticsPresenter);
        }

        void OnFooterBannerSelect(GachaFooterBannerViewModel viewModel)
        {
            _currentTappedOprGachaId = viewModel.OprGachaId;
            UpdateContentView(viewModel.OprGachaId);
        }
        # endregion

        void InitielizeContentView(MasterDataId oprGachaId)
        {
            GachaListViewDelegate.InitializeShowGachaContentView(oprGachaId);
        }

        void UpdateContentView(MasterDataId oprGachaId)
        {
            GachaListViewDelegate.ShowGachaContentView(oprGachaId);
        }

        void IGachaContentViewController.UpdateContentView(
            GachaContentAssetViewModel gachaContentAssetViewModel,
            GachaContentViewModel contentViewModel,
            bool showTransitAnimation)
        {
            var gachaContentAssetPath = gachaContentAssetViewModel.GachaContentAssetPath;

            //アセットロードないときは、ロードしてから表示
            if (!GachaContentAssetContainer.Exists(gachaContentAssetPath))
            {
                GachaListViewDelegate.LoadGachaAsset(
                    gachaContentAssetPath,
                    () =>
                    {
                        var asset = GachaContentAssetContainer.Get(gachaContentAssetPath).GetComponent<GachaContentAssetComponent>();
                        ShouldShowExceptionMessage(asset, contentViewModel);
                        SwitchContent(contentViewModel, asset, showTransitAnimation);
                    });
                return;
            }

            var asset = GachaContentAssetContainer.Get(gachaContentAssetPath).GetComponent<GachaContentAssetComponent>();

            ShouldShowExceptionMessage(asset, contentViewModel);
            SwitchContent(contentViewModel, asset, showTransitAnimation);
        }

        void ShouldShowExceptionMessage(GachaContentAssetComponent asset, GachaContentViewModel contentViewModel)
        {
#if GLOW_DEBUG
            // 存在しないピックアップいたら強制的にException出す
            if (!asset.PickupMstUnitIds
                    .All(a => contentViewModel.PickupMstUnitIds.Exists(vm => vm == a)))
            {
                var message = $"アセット上で想定しないピックアップIdを検知しました。OprGachaDisplayUnitI18nに従ってMstUnitIdを変更してください\n" +
                              $"[アセット名]:\n{asset.name}\n" +
                              $"アセット上のピックアップId一覧:\n{string.Join(",", asset.PickupMstUnitIds.Select(id => id.ToString()))}\n" +
                              $"opr上のピックアップId一覧:\n{string.Join(",", contentViewModel.PickupMstUnitIds.Select(id => id.ToString()))}\n";
                throw new MasterDataNotFoundException(message);
            }
#endif
        }

        void SwitchContent(
            GachaContentViewModel contentViewModel,
            GachaContentAssetComponent asset,
            bool showTransitAnimation)
        {
            if(showTransitAnimation)
            {
                // ガシャコンテンツアセットのトランジション開始
                ActualView.GachaContentView.InitializeGachaContentAssetTransition(() =>
                {
                    // トランジションが画面を覆ったら、コンテンツ更新
                    UpdateContent(contentViewModel, asset);
                });
                // ガシャコンテンツアセットのトランジションアニメーション開始
                ActualView.GachaContentView.StartGachaContentAssetTransitAnimation();
            }
            else
            {
                ActualView.GachaContentView.InitializeGachaContentAssetTransition(() => { });
                UpdateContent(contentViewModel, asset);
            }
        }

        void UpdateContent(
            GachaContentViewModel contentViewModel,
            GachaContentAssetComponent asset)
        {
            ActualView.SetUpGachaDetailButton(
                contentViewModel.ShouldShowRatioButton(),
                contentViewModel.ShouldShowDetailButton()
            );

            // ガシャコンテンツの更新
            ActualView.GachaContentView.SetViewModel(
                contentViewModel,
                asset,
                OnGachaAssetAnimationStart);
        }

        void OnGachaAssetAnimationStart(bool isPickupUnitAnimation)
        {
            ActualView.UpdateUnitButtons(isPickupUnitAnimation);
        }

        # region ガシャバナー
        [UIAction]
        void OnRightBannerCell()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            BannerControlDelegate?.MoveRight();
        }
        [UIAction]
        void OnLeftBannerCell()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            BannerControlDelegate?.MoveLeft();
        }
        #endregion

        #region [UIAction]ガチャ引く関連ボタン
        [UIAction]
        public void OnDrawAdGachaButtonTapped()
        {
            ShowGachaConfirmDialog(_currentTappedOprGachaId, GachaDrawType.Ad);
        }

        [UIAction]
        public void OnSingleDrawButtonTapped()
        {
            ShowGachaConfirmDialog(_currentTappedOprGachaId, GachaDrawType.Single);
        }

        [UIAction]
        public void OnMultiDrawButtonTapped()
        {
            if (_gachaListViewModel.IsTutorialTarget(_currentTappedOprGachaId))
            {
                GachaListViewDelegate.OnTutorialGachaDrawButtonTapped();
                return;
            }

            ShowGachaConfirmDialog(_currentTappedOprGachaId, GachaDrawType.Multi);
        }
        void ShowGachaConfirmDialog(MasterDataId oprGachaId, GachaDrawType gachaDrawType)
        {
            // ガチャ確認ダイアログの表示
            GachaListViewDelegate.ShowGachaConfirmDialogView(oprGachaId, gachaDrawType);
        }
        #endregion


        #region [UIAction]その他ボタン
        [UIAction]
        void ShowGachaRatioDialog()
        {
            // ガシャ提供割合ダイアログの表示
            GachaListViewDelegate.ShowGachaRatioDialogView(_currentTappedOprGachaId);
        }

        [UIAction]
        void ShowGachaLineUpDialog()
        {
            // ガシャラインナップダイアログの表示
            GachaListViewDelegate.ShowGachaLineUpDialogView(_currentTappedOprGachaId);
        }

        [UIAction]
        void ShowGachaDetailDialog()
        {
            // ガシャ詳細ダイアログの表示
            GachaListViewDelegate.ShowGachaDetailDialogView(_currentTappedOprGachaId);
        }

        [UIAction]
        public void OnGachaHistoryButtonTapped()
        {
            // ガシャ履歴
            GachaListViewDelegate.OnGachaHistoryButtonTapped();
        }

        [UIAction]
        public void OnSpecificCommerceButtonTapped()
        {
            // 特商法ボタンタップ時
            GachaListViewDelegate.OnSpecificCommerceButtonTapped();
        }
        #endregion

        [UIAction]
        void OnSpecialAttackButtonTapped()
        {
            // 必殺ワザ表示
            var unitId = ActualView.GachaContentView.CurrentPickupMstUnitId;
            GachaListViewDelegate.OnSpecialAttackButtonTapped(unitId);
        }

        [UIAction]
        public void OnUnitDetailButtonTapped()
        {
            // ユニット詳細ボタンタップ時
            var unitId = ActualView.GachaContentView.CurrentPickupMstUnitId;
            GachaListViewDelegate.OnUnitDetailViewButton(unitId);

            // ユニットアニメーション中の場合はリセット
            // ActualView.AvatarPageList.ResetUnitAnimation();
        }

        [UIAction]
        public void OnChangePickupUnitButtonTapped()
        {
            // ピックアップ変更ボタンタップ時
            ActualView.GachaContentView.NextPickupAreaInformation();
        }

    }
}
