using System;
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
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.GachaAnim.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
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
using GLOW.Scenes.GachaResult.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
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
    public class GachaListPresenter : IGachaListViewDelegate, IGachaDrawControl
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
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] GachaHistoryWireFrame GachaHistoryWireFrame { get; }

        void IGachaListViewDelegate.OnViewDidLoad()
        {
            GachaWireFrame.RegisterGachaListViewController(ViewController);
        }

        void IGachaListViewDelegate.OnViewDidUnLoad()
        {
            GachaWireFrame.UnregisterGachaListViewController();
        }
        GachaListViewModel IGachaListViewDelegate.UpdateListView()
        {
            var useCaseModels = GachaListUseCase.UpdateAndGetGachaListUseCaseModel();

            // 各種バナー設定
            var premiumGachaViewModel =
                GachaListViewModelTranslator.TranslateToPremiumGachaViewModel(useCaseModels.PremiumGachaModel);
            var festivalBannerViewModels =
                GachaListViewModelTranslator.TranslateToFestivalGachaBannerViewModels(useCaseModels.FestivalBannerModels);
            var pickupBannerViewModels =
                GachaListViewModelTranslator.TranslateToGachaBannerViewModels(useCaseModels.PickupBannerModels);
            var freeBannerViewModels =
                GachaListViewModelTranslator.TranslateToGachaBannerViewModels(useCaseModels.FreeBannerModels);
            var ticketBannerViewModels =
                GachaListViewModelTranslator.TranslateToGachaBannerViewModels(useCaseModels.TicketBannerModels);
            var paidOnlyBannerViewModels =
                GachaListViewModelTranslator.TranslateToGachaBannerViewModels(useCaseModels.PaidOnlyBannerModels);
            var medalGachaBannerViewModels =
                GachaListViewModelTranslator.TranslateToMedalGachaBannerViewModel(useCaseModels.MedalBannerModels);
            var tutorialViewModel =
                GachaListViewModelTranslator
                    .TranslateToTutorialGachaBannerViewModel(useCaseModels.TutorialBannerModel);
            var heldAdSkipPassInfoViewModel = HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(
                useCaseModels.HeldAdSkipPassInfoModel);

            if (!tutorialViewModel.IsEmpty())
            {
                // チュートリアルガシャがある場合はスクロールを停止する
                ViewController.DisableScroll();
            }

            return new GachaListViewModel(
                festivalBannerViewModels,
                pickupBannerViewModels,
                freeBannerViewModels,
                ticketBannerViewModels,
                paidOnlyBannerViewModels,
                medalGachaBannerViewModels,
                premiumGachaViewModel,
                tutorialViewModel,
                heldAdSkipPassInfoViewModel
            );
        }

        void IGachaListViewDelegate.OnBannerTapped(MasterDataId gachaId)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha(gachaId)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            var argument = new GachaContentViewController.Argument(gachaId);
            var viewController = ViewFactory.Create<GachaContentViewController,
                GachaContentViewController.Argument>(argument);
            ViewController.SetCurrentGachaContentViewController(viewController);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IGachaListViewDelegate.ShowGachaRatioDialogView(MasterDataId gachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaRatioDialogUseCase.GetGachaRatioUseCaseModel(ct, gachaId);
                var viewModel = GachaRatioDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);

                GachaWireFrame.ShowGachaRatioDialogView(gachaId,viewModel, ViewController.GetViewController);
            });
        }

        void IGachaListViewDelegate.ShowGachaDetailDialogView(MasterDataId gachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaDetailDialogUseCase.GetGachaDetailUseCaseModel(ct, gachaId);
                var viewModel = GachaDetailDialogViewModelTranslator.TranslateToViewModel(useCaseModel);
                GachaWireFrame.ShowGachaDetailView(gachaId, viewModel, ViewController.GetViewController);
            });
        }

        void IGachaListViewDelegate.ShowGachaLineUpDialogView(MasterDataId gachaId)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaLineupDialogUseCase.GetGachaLineupUseCaseModel(ct, gachaId);
                var viewModel = GachaLineupDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);

                GachaWireFrame.ShowGachaLineUpDialogView(gachaId, viewModel, ViewController.GetViewController);
            });
        }

        void OnClickIconDetail(GachaRatioResourceModel resourceModel)
        {
            var playerResourceModel = GetCommonReceiveItemUseCase.GetPlayerResource(
                resourceModel.ResourceType,
                resourceModel.MasterDataId,
                resourceModel.Amount
            );

            GachaWireFrame.OnClickIconDetail(playerResourceModel, ViewController.GetViewController);
        }

        GachaContentViewController IGachaListViewDelegate.CreateGachaContentViewController(MasterDataId gachaId)
        {
            var argument = new GachaContentViewController.Argument(gachaId);
            return ViewFactory.Create<GachaContentViewController, GachaContentViewController.Argument>(argument);
        }

        bool IGachaListViewDelegate.ShowGachaConfirmDialogView(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha(gachaId)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return false;
            }

            var argument = new GachaConfirmDialogViewController.Argument(gachaId,gachaDrawType);
            var viewController = ViewFactory.Create<
                GachaConfirmDialogViewController,
                GachaConfirmDialogViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            return true;
        }

        void IGachaListViewDelegate.OnTutorialGachaDrawButtonTapped()
        {
            TutorialGachaDraw(false);
        }

        void IGachaListViewDelegate.OnGachaHistoryButtonTapped()
        {
            var controller = ViewController as UIViewController;
            GachaHistoryWireFrame.ShowGachaHistoryDialogView(ViewController.GetActualView, controller);
        }

        void IGachaDrawControl.UpdateContentView()
        {
            ViewController.UpdateCurrentGachaContentViewController();
        }

        void IGachaDrawControl.GachaDraw(
            MasterDataId gachaId,
            GachaType gachaType,
            GachaDrawCount drawCount,
            CostType costType,
            GachaDrawType gachaDrawType,
            CostAmount costAmount,
            MasterDataId costId,
            bool isReDraw,
            GachaDrawFromContentViewFlag gachaDrawFromContentViewFlag)
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
                        gachaId: gachaId,
                        gachaType: gachaType,
                        gachaDrawCount: drawCount,
                        costType: costType,
                        gachaDrawType: gachaDrawType,
                        gachaDrawFromContentViewFlag: gachaDrawFromContentViewFlag,
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
                    await GachaResult(ct, isReDraw);
                }
                catch (MstNotFoundException)
                {
                    // 期間外ガシャ
                    ShowGachaOutOfPeriodMessage(isReDraw, gachaDrawFromContentViewFlag);
                }
                catch (GachaExpiredException)
                {
                    // N時間開放ガシャの有効期限が切れている
                    ShowGachaOutOfPeriodMessage(isReDraw, gachaDrawFromContentViewFlag);
                }
                catch (OperationCanceledException)
                {
                    // キャンセルされた場合は何もしない
                    Debug.LogWarning("operation was cancelled.");
                }
            });
        }

        async UniTask GachaResult(CancellationToken cancellationToken, bool isReDraw)
        {
            // ガシャ演出
            var animViewController = ViewFactory.Create<GachaAnimViewController>();
            HomeViewNavigation.PushUnmanagedView(animViewController, HomeContentDisplayType.FullScreenOverlap);

            // ガシャ結果を表示して待つ
            var viewController = ViewFactory.Create<GachaResultViewController>();

            // ガシャ結果から引いた場合、背景を消す対応
            if (isReDraw)
            {
                HomeViewNavigation.TryPop(
                    false,
                    completion: () => HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap));
            }
            else
            {
                HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
            }

            // 演出再生のためScreenInteractionを無効化
            ScreenInteractionControl.Disable();

            await animViewController.WaitAnimation(cancellationToken);

            // ガシャ結果を再生する
            viewController.StartAnimation();
        }

        void IGachaDrawControl.TutorialGachaDraw(bool isReDraw)
        {
            TutorialGachaDraw(isReDraw);
        }

        void TutorialGachaDraw(bool isReDraw)
        {
            DoAsync.Invoke(ViewController.GetActualView, ScreenInteractionControl, async ct =>
            {
                await TutorialGachaDrawUseCase.GachaDraw(ct);

                // ガシャ演出アセットのロード
                await GachaAnimationLoadUseCase.LoadGachaAnimAsset(ct);

                // ガシャ演出結果
                await GachaResult(ct, isReDraw);
            });
        }

        void ShowGachaOutOfPeriodMessage(bool isReDraw,
            GachaDrawFromContentViewFlag isGachaDrawFromContentView)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "こちらのガシャの開催期間は終了しました。\nガシャ一覧画面に移動します。",
                "",
                () =>
                {
                    if (isReDraw)
                    {
                        if (isGachaDrawFromContentView)
                        {
                            // GachaContent -> ReDrawは２回戻す
                            HomeViewNavigation.TryPop(false, () => HomeViewNavigation.TryPop());
                        }
                        else
                        {
                            // ガシャ一覧からReDrawは１回戻す
                            HomeViewNavigation.TryPop();
                        }
                    }
                    // ガシャ一覧に戻す
                    else if (isGachaDrawFromContentView)
                    {
                        // ガシャトップから引いた場合はガシャ一覧に戻す
                        HomeViewNavigation.TryPop();
                    }
                    else
                    {
                        ViewController.UpdateView();
                    }
                });
        }
    }
}
