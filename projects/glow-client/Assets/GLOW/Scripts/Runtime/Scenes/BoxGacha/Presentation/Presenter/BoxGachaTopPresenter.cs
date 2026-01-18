using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.BoxGacha.Domain.UseCase;
using GLOW.Scenes.BoxGacha.Presentation.Translator;
using GLOW.Scenes.BoxGacha.Presentation.View;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using GLOW.Scenes.BoxGacha.Presentation.WireFrame;
using GLOW.Scenes.BoxGachaConfirm.Presentation.View;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.View;
using GLOW.Scenes.BoxGachaResult.Presentation.Translator;
using GLOW.Scenes.BoxGachaResult.Presentation.View;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Presentation.Presenter
{
    public class BoxGachaTopPresenter : IBoxGachaTopViewDelegate
    {
        [Inject] BoxGachaTopViewController ViewController { get; }
        [Inject] BoxGachaTopViewController.Argument Argument { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] DrawBoxGachaUseCase DrawBoxGachaUseCase { get; }
        [Inject] ResetBoxGachaUseCase ResetBoxGachaUseCase { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] BoxGachaExceptionWireFrame BoxGachaExceptionWireFrame { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        
        CancellationToken BoxGachaTopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        MasterDataId _mstBoxGachaId;
        BoxGachaInfoViewModel _boxGachaInfoViewModel;
        
        void IBoxGachaTopViewDelegate.OnViewDidLoad()
        {
            _mstBoxGachaId = Argument.ViewModel.MstBoxGachaId;
            _boxGachaInfoViewModel = Argument.ViewModel.BoxGachaInfoViewModel;
            
            DoAsync.Invoke(BoxGachaTopCancellationToken, async cancellationToken =>
            {
                LoadAndSetupDecoUnits(
                    cancellationToken,
                    Argument.ViewModel.DisplayDecoUnitFirst,
                    Argument.ViewModel.DisplayDecoEnemyUnitSecond).Forget();
                ViewController.SetUpBoxGachaTopBackground(Argument.ViewModel.KomaBackgroundAssetPath);
                ViewController.SetUpBoxGachaInfo(_boxGachaInfoViewModel);
            });
        }

        void IBoxGachaTopViewDelegate.OnCloseButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IBoxGachaTopViewDelegate.OnBoxGachaLineupButtonTapped()
        {
            var argument = new BoxGachaLineupDialogViewController.Argument(
                _mstBoxGachaId,
                _boxGachaInfoViewModel.CurrentBoxLevel);
            var viewController = ViewFactory.Create<
                BoxGachaLineupDialogViewController, 
                BoxGachaLineupDialogViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }

        void IBoxGachaTopViewDelegate.OnBoxGachaResetButtonTapped()
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                "切り替え確認",
                "次の「いいジャンくじ」へ切り替えますか？",
                "※現在の「いいジャンくじ」に含まれる未獲得の報酬は、これ以降獲得できなくなります。",
                "切り替え",
                "キャンセル",
                action1: ExecuteResetBoxGacha);
        }

        void IBoxGachaTopViewDelegate.OnBoxGachaDrawButtonTapped()
        {
            var argument = new BoxGachaConfirmDialogViewController.Argument(_mstBoxGachaId);
            var viewController = ViewFactory.Create<
                BoxGachaConfirmDialogViewController, 
                BoxGachaConfirmDialogViewController.Argument>(argument);
            viewController.OnDrawButtonTappedAction = ExecuteDrawBoxGacha;
            ViewController.PresentModally(viewController);
        }

        void IBoxGachaTopViewDelegate.OnPrizeIconTapped(PlayerResourceIconViewModel prizeIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                prizeIconViewModel, 
                ViewController, 
                MaxStatusFlag.False);
        }

        async UniTask LoadAndSetupDecoUnits(
            CancellationToken cancellationToken,
            UnitImageAssetPath displayUnitFirst,
            UnitImageAssetPath displayUnitSecond)
        {
            // キャラクターをロード
            var unitLoadTasks = new List<UniTask>();
            if (!displayUnitFirst.IsEmpty())
            {
                unitLoadTasks.Add(UnitImageLoader.Load(cancellationToken, displayUnitFirst));
            }

            if (!displayUnitSecond.IsEmpty())
            {
                unitLoadTasks.Add(UnitImageLoader.Load(cancellationToken, displayUnitSecond));
            }

            await UniTask.WhenAll(unitLoadTasks);

            // 敵キャラクターを表示
            ViewController.SetUpDecoUnitImage(
                displayUnitFirst,
                displayUnitSecond);
        }

        void ExecuteDrawBoxGacha(GachaDrawCount drawCount)
        {
            DoAsync.Invoke(BoxGachaTopCancellationToken, async cancellationToken =>
            {
                try
                {
                    ViewController.SetButtonInteractable(false);
                    var drawResult = await DrawBoxGachaUseCase.Draw(
                        Argument.MstEventId,
                        drawCount,
                        cancellationToken);

                    var resultViewModel = BoxGachaResultViewModelTranslator.Translate(drawResult);
                    var argument = new BoxGachaResultViewController.Argument(resultViewModel);
                    var viewController = ViewFactory.Create<
                        BoxGachaResultViewController,
                        BoxGachaResultViewController.Argument>(argument);
                    ViewController.PresentModally(viewController);

                    // 引いた後のBoxガチャ情報で表示を更新
                    var updatedBoxGachaInfoViewModel = BoxGachaInfoViewModelTranslator.ToBoxGachaInfoViewModel(
                        drawResult.DrawnBoxGachaInfoModel);
                    ViewController.SetUpBoxGachaInfo(updatedBoxGachaInfoViewModel);

                    // Homeヘッダーのステータスも更新
                    HomeHeaderDelegate.UpdateStatus();
                    
                    // 必ずボタンを再度有効化する
                    ViewController.SetButtonInteractable(true);
                }
                catch (BoxGachaCostNotEnoughException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageCostNotEnough();
                }
                catch (BoxGachaDrawCountExceededException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageDrawCountExceeded(() =>
                    {
                        ApplicationRebootor.Reboot();
                    });
                }
                catch (BoxGachaPeriodOutsideException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(() =>
                    {
                        HomeViewNavigation.TryPop();
                    });
                }
                catch (BoxGachaStockNotEnoughException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageStockNotEnough();
                }
                catch (ItemAmountIsNotEnoughException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageItemAmountIsNotEnough();
                }
                finally
                {
                    // 必ずボタンを再度有効化する
                    ViewController.SetButtonInteractable(true);
                }
            });
        }

        void ExecuteResetBoxGacha()
        {
            DoAsync.Invoke(BoxGachaTopCancellationToken, async cancellationToken =>
            {
                try
                {
                    ViewController.SetButtonInteractable(false);
                    var resetTask = ResetBoxGachaUseCase.Reset(
                        Argument.MstEventId,
                        cancellationToken);
                    var inAnimationTask = ViewController.PlayLineupResetInAnimation(cancellationToken)
                        .AsAsyncUnitUniTask();

                    var (resetInfoModel, _) = await UniTask.WhenAll(resetTask, inAnimationTask);

                    // リセット後のBoxガチャ情報で表示を更新
                    var updatedBoxGachaInfoViewModel =
                        BoxGachaInfoViewModelTranslator.ToBoxGachaInfoViewModel(resetInfoModel);
                    ViewController.SetUpBoxGachaInfo(updatedBoxGachaInfoViewModel);
                    ViewController.ResetRewardListScrollPosition();

                    await ViewController.PlayLineupResetOutAnimation(cancellationToken);
                    ViewController.SetButtonInteractable(true);
                }
                catch (BoxGachaDrawCountExceededException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageDrawCountExceeded(() =>
                    {
                        ApplicationRebootor.Reboot();
                    });
                }
                catch (BoxGachaPeriodOutsideException)
                {
                    BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(() =>
                    {
                        HomeViewNavigation.TryPop();
                    });
                }
                finally
                {
                    await ViewController.PlayLineupResetOutAnimation(cancellationToken);
                    ViewController.SetButtonInteractable(true);
                }
            });
        }
    }
}