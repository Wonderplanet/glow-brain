using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Exceptions;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaContent.Domain.UseCases;
using GLOW.Scenes.GachaContent.Presentation.Translator;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Presenters;
using GLOW.Scenes.GachaLineupDialog.Domain.UseCases;
using GLOW.Scenes.GachaLineupDialog.Presentation.Translator;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Translator;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaList.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-5_ガシャ一覧画面
    /// </summary>
    public class GachaListPresenter : IGachaListViewDelegate,
        IGachaDrawControl,
        IGachaListContentViewDelegate
    {
        [Inject] IGachaListViewController ViewController { get; }
        [Inject] GachaListUseCase GachaListUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GachaDrawUseCase GachaDrawUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }
        [Inject] GachaAnimationLoadUseCase GachaAnimationLoadUseCase { get; }
        [Inject] GetCommonReceiveItemUseCase GetCommonReceiveItemUseCase { get; }
        [Inject] GachaRatioDialogUseCase GachaRatioDialogUseCase { get; }
        [Inject] GachaLineupDialogUseCase GachaLineupDialogUseCase { get; }
        [Inject] GachaDetailDialogUseCase GachaDetailDialogUseCase { get; }
        [Inject] TutorialGachaDrawUseCase TutorialGachaDrawUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        [Inject] GachaHistoryWireFrame GachaHistoryWireFrame { get; }

        // ContentView
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] IGachaListElementUseCaseModelFactory GachaListElementUseCaseModelFactory { get; }
        [Inject] IGachaContentViewController GachaContentViewController { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IGachaContentAssetLoader GachaContentAssetLoader { get; }
        [Inject] IGachaContentAssetContainer GachaContentAssetContainer { get; }


        bool _initialzedView = false;
        bool _initializedAsset = false;

        void IGachaListViewDelegate.OnViewDidLoad()
        {
            GachaWireFrame.RegisterGachaListViewController(ViewController);

            // 初回アセットロード
            var listViewModel = CreateGachaListViewModel(MasterDataId.Empty);
            InitializeGachaContentAsset(listViewModel, null);
        }

        void IGachaListViewDelegate.OnViewWillAppear()
        {
            // 結果画面からなど、ガシャ画面が再表示されたときメンテナンスチェックする
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha()))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            InitializeView(MasterDataId.Empty);
        }

        void IGachaListViewDelegate.OnViewDidUnLoad()
        {
            GachaWireFrame.UnregisterGachaListViewController();
        }

        void InitializeView(MasterDataId initialShowOprGachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                await UniTask.WaitUntil(() => _initializedAsset, cancellationToken: ct);
                var listViewModel = CreateGachaListViewModel(initialShowOprGachaId);
                ViewController.InitializeView(listViewModel);
                _initialzedView = true;
            });
        }

        void InitializeGachaContentAsset(GachaListViewModel listViewModel, Action onCompleted)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                foreach (var assetPath in listViewModel.GetAllGachaContentAssetPaths())
                {
                    if (GachaContentAssetContainer.Exists(assetPath))
                    {
                        continue;
                    }

                    await LoadGachaAsset(ct, assetPath, null);
                }

                onCompleted?.Invoke();
                _initializedAsset = true;
            });
        }

        void IGachaListViewDelegate.UpdateView(MasterDataId initialShowOprGachaId)
        {
            UpdateView(initialShowOprGachaId);
        }

        void IGachaListViewDelegate.LoadGachaAsset(
            GachaContentAssetPath gachaContentAssetPath,
            Action onCompleted)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                await LoadGachaAsset(ct, gachaContentAssetPath, onCompleted);
            });
        }

        async UniTask LoadGachaAsset(
            CancellationToken ct,
            GachaContentAssetPath gachaContentAssetPath,
            Action onCompleted)
        {
            // すでにロードされている場合はスキップ
            if (GachaContentAssetContainer.Exists(gachaContentAssetPath)) return;

            await GachaContentAssetLoader.Load(ct, gachaContentAssetPath);
            onCompleted?.Invoke();
        }


        void IGachaListViewDelegate.ShowGachaRatioDialogView(MasterDataId oprGachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaRatioDialogUseCase.GetGachaRatioUseCaseModel(ct, oprGachaId);
                var viewModel = GachaRatioDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);

                GachaWireFrame.ShowGachaRatioDialogView(oprGachaId, viewModel, ViewController.GetViewController);
            });
        }

        void IGachaListViewDelegate.ShowGachaDetailDialogView(MasterDataId oprGachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaDetailDialogUseCase.GetGachaDetailUseCaseModel(ct, oprGachaId);
                var viewModel = GachaDetailDialogViewModelTranslator.TranslateToViewModel(useCaseModel);
                GachaWireFrame.ShowGachaDetailView(oprGachaId, viewModel, ViewController.GetViewController);
            });
        }

        void IGachaListViewDelegate.OnSpecificCommerceButtonTapped()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IGachaListViewDelegate.ShowGachaLineUpDialogView(MasterDataId oprGachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaLineupDialogUseCase.GetGachaLineupUseCaseModel(ct, oprGachaId);
                var viewModel = GachaLineupDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);

                GachaWireFrame.ShowGachaLineUpDialogView(oprGachaId, viewModel, ViewController.GetViewController);
            });
        }

        #region IGachaListContentViewDelegate

        void IGachaListContentViewDelegate.InitializeShowGachaContentView(MasterDataId oprGachaId)
        {
            ShowGachaContentView(oprGachaId, false);
        }
        void IGachaListContentViewDelegate.ShowGachaContentView(MasterDataId oprGachaId)
        {
            ShowGachaContentView(oprGachaId, true);
        }

        void ShowGachaContentView(MasterDataId oprGachaId, bool showTransitAnimation)
        {
            var viewModel = GetElementViewModelAtSingle(oprGachaId);

            GachaContentViewController.UpdateContentView(
                viewModel.GachaContentAssetViewModel,
                viewModel.GachaContentViewModel,
                showTransitAnimation);
        }

        # region ViewModel作るメソッドたち
        GachaListViewModel CreateGachaListViewModel(MasterDataId initialOprGachaId)
        {
            var useCaseModels = GachaListUseCase.UpdateAndGetGachaListUseCaseModel(initialOprGachaId);
            return GachaListViewModelTranslator.Translate(useCaseModels);
        }

        GachaListElementViewModel GetElementViewModelAtSingle(MasterDataId oprGachaId)
        {
            var model = GachaListElementUseCaseModelFactory.Create(oprGachaId);
            return GachaListElementTranslator.TranslateElement(model);
        }
        #endregion

        void IGachaListContentViewDelegate.OnSpecialAttackButtonTapped(MasterDataId unitId)
        {
            var argument = new UnitSpecialAttackPreviewViewController.Argument(unitId);
            var viewController = ViewFactory.Create<UnitSpecialAttackPreviewViewController, UnitSpecialAttackPreviewViewController.Argument>(argument);

            // UpdatedDisplayUnitScroll();

            // ヘッダーよりも上に表示するため、HomeViewControllerを使って表示する
            HomeViewController.Show(viewController);
        }

        void UpdatedDisplayUnitScroll()
        {
            // ViewController.DisableUnitControlScroll();
            // viewController.OnClose = () =>
            // {
            //     ViewController.EnableScroll();
            // };
        }

        void IGachaListContentViewDelegate.OnUnitDetailViewButton(MasterDataId unitId)
        {
            var argument = new UnitDetailModalViewController.Argument(unitId, MaxStatusFlag.True);
            var controller = ViewFactory.Create<UnitDetailModalViewController, UnitDetailModalViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        #endregion

        void OnClickIconDetail(GachaRatioResourceModel resourceModel)
        {
            var playerResourceModel = GetCommonReceiveItemUseCase.GetPlayerResource(
                resourceModel.ResourceType,
                resourceModel.MasterDataId,
                resourceModel.Amount
            );

            GachaWireFrame.OnClickIconDetail(playerResourceModel, ViewController.GetViewController);
        }

        bool IGachaListViewDelegate.ShowGachaConfirmDialogView(MasterDataId oprGachaId, GachaDrawType gachaDrawType)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha(oprGachaId)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return false;
            }

            var argument = new GachaConfirmDialogViewController.Argument(oprGachaId, gachaDrawType);
            var viewController = ViewFactory.Create<
                GachaConfirmDialogViewController,
                GachaConfirmDialogViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            return true;
        }

        void IGachaListViewDelegate.OnTutorialGachaDrawButtonTapped()
        {
            DrawTutorialGacha(false);
        }

        void IGachaListViewDelegate.OnGachaHistoryButtonTapped()
        {
            var controller = ViewController as UIViewController;
            GachaHistoryWireFrame.ShowGachaHistoryDialogView(ViewController.GetActualView, controller);
        }

        void IGachaDrawControl.UpdateView(MasterDataId drawOprGachaId)
        {
            UpdateView(drawOprGachaId);
        }

        void UpdateView(MasterDataId initialShowOprGachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                await UniTask.WaitUntil(() => _initialzedView, cancellationToken: ct);
                // 内容更新
                var viewModel = CreateGachaListViewModel(initialShowOprGachaId);
                ViewController.UpdateView(viewModel);
            });
        }

        void IGachaDrawControl.GachaDraw(
            MasterDataId oprGachaId,
            GachaType gachaType,
            GachaDrawCount drawCount,
            CostType costType,
            GachaDrawType gachaDrawType,
            CostAmount costAmount,
            MasterDataId costId,
            bool isReDraw)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                try
                {
                    if (costType == CostType.Ad &&
                        GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
                    {
                        // 広告ガチャの時は広告を再生する
                        var result = await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.Gacha, ct);
                        // 広告キャンセルされたら何もしない
                        if (result == AdResultType.Cancelled)
                        {
                            return;
                        }
                    }

                    await GachaDrawUseCase.GachaDraw(
                        ct,
                        gachaId: oprGachaId,
                        gachaType: gachaType,
                        gachaDrawCount: drawCount,
                        costType: costType,
                        gachaDrawType: gachaDrawType,
                        costAmount: costAmount,
                        costId: costId
                    );

                    if (costType == CostType.Ad)
                    {
                        // 広告ガチャ引いた後にローカル通知のスケジュールを更新
                        LocalNotificationScheduler.RefreshRemainAdGachaSchedule();
                    }

                    // フッターとヘッダーの更新
                    HomeHeaderDelegate.UpdateStatus();
                    HomeFooterDelegate.UpdateBadgeStatus();

                    // ガシャ演出アセットのロード
                    await GachaAnimationLoadUseCase.LoadGachaAnimAsset(ct);

                    // ガシャ演出結果
                    await GachaWireFrame.ShowGachaResult(ct, isReDraw, ScreenInteractionControl);
                }
                catch (MstNotFoundException)
                {
                    // 期間外ガシャ
                    GachaWireFrame.ShowGachaOutOfPeriodMessage(this, isReDraw);
                }
                catch (GachaExpiredException)
                {
                    // N時間開放ガシャの有効期限が切れている
                    GachaWireFrame.ShowGachaOutOfPeriodMessage(this, isReDraw);
                }
                catch (OperationCanceledException)
                {
                    // キャンセルされた場合は何もしない
                    Debug.LogWarning("operation was cancelled.");
                }
            });
        }

        void IGachaDrawControl.DrawTutorialGacha(bool isReDraw)
        {
            DrawTutorialGacha(isReDraw);
        }

        void DrawTutorialGacha(bool isReDraw)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                await TutorialGachaDrawUseCase.GachaDraw(ct);

                // ガシャ演出アセットのロード
                await GachaAnimationLoadUseCase.LoadGachaAnimAsset(ct);

                // ガシャ演出結果
                await GachaWireFrame.ShowGachaResult(ct, isReDraw, ScreenInteractionControl);
            });
        }
    }
}
