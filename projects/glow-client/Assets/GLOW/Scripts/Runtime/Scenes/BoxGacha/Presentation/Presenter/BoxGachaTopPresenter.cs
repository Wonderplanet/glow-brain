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
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
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
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }
        
        CancellationToken BoxGachaTopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        MasterDataId _mstBoxGachaId;
        MasterDataId _mstEventId;
        BoxGachaInfoViewModel _boxGachaInfoViewModel;
        
        void IBoxGachaTopViewDelegate.OnViewDidLoad()
        {
            _mstBoxGachaId = Argument.ViewModel.MstBoxGachaId;
            _mstEventId = Argument.MstEventId;
            _boxGachaInfoViewModel = Argument.ViewModel.BoxGachaInfoViewModel;
            
            DoAsync.Invoke(BoxGachaTopCancellationToken, async cancellationToken =>
            {
                LoadAndSetupDecoUnits(
                    cancellationToken,
                    Argument.ViewModel.DisplayDecoUnitFirst,
                    Argument.ViewModel.DisplayDecoEnemyUnitSecond).Forget();
                ViewController.SetUpBoxGachaTopBackground(Argument.ViewModel.KomaBackgroundAssetPath);
                ViewController.SetUpBoxGachaName(Argument.ViewModel.BoxGachaName);
                ViewController.SetUpBoxGachaInfo(_boxGachaInfoViewModel);
            });
        }

        void IBoxGachaTopViewDelegate.OnCloseButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IBoxGachaTopViewDelegate.OnBoxGachaLineupButtonTapped()
        {
            if (!EventOpenCheckUseCase.IsOpenEvent(_mstEventId))
            {
                BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
                return;
            }
            
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
            if (!EventOpenCheckUseCase.IsOpenEvent(_mstEventId))
            {
                BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
                return;
            }
            
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
            if (!EventOpenCheckUseCase.IsOpenEvent(_mstEventId))
            {
                BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
                return;
            }
            
            // 在庫がなくなっている場合はリセットを促す
            if (_boxGachaInfoViewModel.CurrentBoxTotalDrawnCount == _boxGachaInfoViewModel.TotalStockCount)
            {
                BoxGachaExceptionWireFrame.ShowMessageStockNotEnough();
                return;
            }
            
            var argument = new BoxGachaConfirmDialogViewController.Argument(_mstEventId);
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
            DoAsync.Invoke(BoxGachaTopCancellationToken, ScreenInteractionControl, async cancellationToken =>
            {
                using (ViewController.ViewTapGuard())
                {
                    await ExecuteDrawBoxGachaAsync(cancellationToken, drawCount);
                }
            });
        }

        async UniTask ExecuteDrawBoxGachaAsync(CancellationToken cancellationToken, GachaDrawCount drawCount)
        {
            try
            {
                var drawResult = await DrawBoxGachaUseCase.Draw(
                    Argument.MstEventId,
                    drawCount,
                    cancellationToken);

                if (drawResult.IsEmpty())
                {
                    BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
                    return;
                }

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
                
                // 参照する値も更新
                _boxGachaInfoViewModel = updatedBoxGachaInfoViewModel;

                // Homeヘッダーのステータスも更新
                HomeHeaderDelegate.UpdateStatus();
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
                BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
            }
            catch (BoxGachaStockNotEnoughException)
            {
                BoxGachaExceptionWireFrame.ShowMessageStockNotEnough();
            }
            catch (ItemAmountIsNotEnoughException)
            {
                BoxGachaExceptionWireFrame.ShowMessageItemAmountIsNotEnough();
            }
        }

        void ExecuteResetBoxGacha()
        {
            DoAsync.Invoke(BoxGachaTopCancellationToken, async cancellationToken =>
            {
                using (ViewController.ViewTapGuard())
                {
                    await ExecuteResetBoxGachaAsync(cancellationToken);
                }
            });
        }

        async UniTask ExecuteResetBoxGachaAsync(CancellationToken cancellationToken)
        {
            try
            {
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

                // 参照する値も更新
                _boxGachaInfoViewModel = updatedBoxGachaInfoViewModel;
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
                BoxGachaExceptionWireFrame.ShowMessageAfterBoxGachaPeriod(TransitHomeTop);
            }
            finally
            {
                await ViewController.PlayLineupResetOutAnimation(cancellationToken);
            }
        }
        
        void TransitHomeTop()
        {
            if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeViewNavigation.TryPopToRoot();
            }
            else
            {
                HomeViewNavigation.Switch(HomeContentTypes.Main);
            }
        }
    }
}