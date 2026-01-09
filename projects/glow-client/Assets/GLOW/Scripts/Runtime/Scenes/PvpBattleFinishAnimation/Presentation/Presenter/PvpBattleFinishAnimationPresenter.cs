using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.PvpBattleFinishAnimation.Presentation.Presenter
{
    public class PvpBattleFinishAnimationPresenter : IPvpBattleFinishAnimationViewDelegate
    {
        [Inject] PvpBattleFinishAnimationViewController ViewController { get; }
        [Inject] PvpBattleFinishAnimationViewController.Argument Argument { get; }

        CancellationToken PvpFinishCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        bool _isAnimationFinished = false;

        void IPvpBattleFinishAnimationViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeProgressBar(Argument.ViewModel.ResultType);
            
            DoAsync.Invoke(PvpFinishCancellationToken, async cancellationToken =>
            {
                await ViewController.PlayFinishAnimation(
                    cancellationToken,
                    Argument.ViewModel);
                
                _isAnimationFinished = true;
            });
        }

        void IPvpBattleFinishAnimationViewDelegate.OnScreenTapped()
        {
            if (!_isAnimationFinished)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            ViewController.OnCloseButtonTappedAction?.Invoke();
            ViewController.Dismiss();
        }
    }
}