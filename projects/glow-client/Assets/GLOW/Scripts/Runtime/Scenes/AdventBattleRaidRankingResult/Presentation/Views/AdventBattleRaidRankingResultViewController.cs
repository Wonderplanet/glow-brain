using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    ///  　44-4-3_ランキング結果表示ダイアログ
    /// 　　　44-4-3-1_ランキング結果表示（協力バトル）ダイアログ
    /// </summary>
    public class AdventBattleRaidRankingResultViewController : UIViewController<AdventBattleRaidRankingResultView>
    {
        public record Argument(AdventBattleRankingResultViewModel AdventBattleRankingResultViewModel, Action OnCloseView);

        [Inject] IAdventBattleRaidRankingResultViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(AdventBattleRankingResultViewModel viewModel)
        {
            ActualView.SetUpEnemyComponent(
                viewModel.EnemyImageAssetPath,
                UnitImageLoader,
                UnitImageContainer);
            ActualView.SetUpTitle(viewModel.AdventBattleName);
            ActualView.SetUpScoreText(viewModel.Score);
        }
        
        public void SkipSlideInAnimation()
        {
            ActualView.SkipSlideInAnimation();
        }
        
        public void SkipEnemyIconAnimation()
        {
            ActualView.SkipEnemyIconAnimation();
        }

        public void PlayEnemyLoopAnimation()
        {
            ActualView.PlayEnemyLoopAnimation();
        }
        
        public void SkipRewardAnimation(AdventBattleRankingResultViewModel viewModel)
        {
            ActualView.SkipRewardPanelAnimation();
            ActualView.SkipAcquiredItemsAnimation(viewModel.RewardList);
        }

        [UIAction]
        void OnViewTapped()
        {
            ViewDelegate.OnViewTapped();
        }
    }
}
