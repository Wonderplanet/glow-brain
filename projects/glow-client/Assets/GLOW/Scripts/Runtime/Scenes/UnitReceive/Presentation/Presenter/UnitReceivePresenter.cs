using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.UnitReceive.Domain.UseCase;
using GLOW.Scenes.UnitReceive.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Presentation.Presenter
{
    public class UnitReceivePresenter : IUnitReceiveViewDelegate
    {
        [Inject] UnitReceiveViewController ViewController { get; }
        [Inject] UnitReceiveViewController.Argument Argument { get; }
        [Inject] DisplayReceivedUnitUseCase DisplayReceivedUnitUseCase { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }

        CancellationToken UnitReceiveCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _onCloseButtonCalled;

        void IUnitReceiveViewDelegate.OnViewWillAppear()
        {
            ViewController.ActualView.Hidden = true;

            DoAsync.Invoke(UnitReceiveCancellationToken, async cancellationToken =>
            {
                // 受け取ったユニットのモーション情報を取得
                // UnLoadはシーン遷移時に行う
                var unitImageLoadTask = UnitImageLoader.Load(cancellationToken, Argument.ViewModel.UnitImageAssetPath);
                var delayTask = UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);
                await UniTask.WhenAll(unitImageLoadTask, delayTask);

                ViewController.ActualView.Hidden = false;
                ViewController.SetUpView(Argument.ViewModel);
                await ViewController.PlayOpenAnimation(cancellationToken);
            });
        }

        void IUnitReceiveViewDelegate.OnCloseButtonTapped(Action onCloseCompletion)
        {
            if (_onCloseButtonCalled) return;

            DoAsync.Invoke(UnitReceiveCancellationToken, async cancellationToken =>
            {
                _onCloseButtonCalled = true;
                await ViewController.PlayCloseAnimation(cancellationToken);
                ViewController.Dismiss(completion:onCloseCompletion);
            });
        }
    }
}
