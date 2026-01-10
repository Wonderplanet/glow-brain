using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using Zenject;
namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.Presenters
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-3_ランキング結果表示ダイアログ
    /// </summary>
    public class AdventBattleRankingResultPresenter : IAdventBattleRankingResultViewDelegate
    {
        [Inject] AdventBattleRankingResultViewController ViewController { get; }
        [Inject] AdventBattleRankingResultViewController.Argument Argument { get; }

        CancellationToken AdventBattleRankingResultCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        bool _isAnimationComplete = false;

        void IAdventBattleRankingResultViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUp(Argument.AdventBattleRankingResultViewModel);
        }

        void IAdventBattleRankingResultViewDelegate.OnViewDidAppear()
        {
            DoAsync.Invoke(AdventBattleRankingResultCancellationToken, async cancellationToken =>
            {
                using (ViewController.ViewTapGuard())
                {
                    await ViewController.PlayAnimation(Argument.AdventBattleRankingResultViewModel, cancellationToken);
                    
                    _isAnimationComplete = true;
                }
            });
        }

        void IAdventBattleRankingResultViewDelegate.OnCloseButtonTapped()
        {
            Argument.OnCloseView?.Invoke();
            ViewController.Dismiss();
        }

        void IAdventBattleRankingResultViewDelegate.OnEscape()
        {
            if (!_isAnimationComplete)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            Argument.OnCloseView?.Invoke();
            ViewController.Dismiss();
        }
    }
}
