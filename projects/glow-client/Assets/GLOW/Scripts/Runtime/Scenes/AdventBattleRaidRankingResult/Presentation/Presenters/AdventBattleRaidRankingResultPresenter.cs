using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using WonderPlanet.UniTaskSupporter;
using Zenject;
namespace GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Presenters
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    ///  　44-4-3_ランキング結果表示ダイアログ
    /// 　　　44-4-3-1_ランキング結果表示（協力バトル）ダイアログ
    /// </summary>
    public class AdventBattleRaidRankingResultPresenter : IAdventBattleRaidRankingResultViewDelegate
    {
        [Inject] AdventBattleRaidRankingResultViewController ViewController { get; }
        [Inject] AdventBattleRaidRankingResultViewController.Argument Argument { get; }

        public void OnViewDidLoad()
        {
            ViewController.Setup(Argument.AdventBattleRankingResultViewModel);
            
            // ランキング結果画面の後に出す関係で、アニメーションはさせない
            ViewController.SkipSlideInAnimation();
            ViewController.SkipEnemyIconAnimation();
            PlayEnemyLoopAnimation();
            ViewController.SkipRewardAnimation(Argument.AdventBattleRankingResultViewModel);
        }
        
        void PlayEnemyLoopAnimation()
        {
            ViewController.PlayEnemyLoopAnimation();
        }
        
        public void OnViewTapped()
        {
            Argument.OnCloseView?.Invoke();
            ViewController.Dismiss();
        }
    }
}
