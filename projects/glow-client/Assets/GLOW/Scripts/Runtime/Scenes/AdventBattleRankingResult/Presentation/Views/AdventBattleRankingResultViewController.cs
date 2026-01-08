using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-3_ランキング結果表示ダイアログ
    /// </summary>
    public class AdventBattleRankingResultViewController : 
        UIViewController<AdventBattleRankingResultView>,
        IEscapeResponder,
        IAsyncActivityControl
    {
        public record Argument(AdventBattleRankingResultViewModel AdventBattleRankingResultViewModel, Action OnCloseView);

        [Inject] IAdventBattleRankingResultViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
            
            ViewDelegate.OnViewDidAppear();
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }
        
        public void SetUp(AdventBattleRankingResultViewModel viewModel)
        {
            ActualView.SetUpAdventBattleTitle(viewModel.AdventBattleName);
            ActualView.SetUpRankIcon(viewModel.RankType, viewModel.RankLevel);
            ActualView.SetUpRankTierText(viewModel.Rank);
            ActualView.SetUpRankText(viewModel.RankType, viewModel.RankLevel);
            ActualView.SetUpScoreText(viewModel.Score);
        }
        
        public async UniTask PlayAnimation(
            AdventBattleRankingResultViewModel viewModel,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRankAnimation(viewModel.Rank, cancellationToken);
            await ActualView.PlayAcquiredItemsAnimation(viewModel.RewardList, cancellationToken);
            ActualView.SetUpCloseTextAndButton();
        }
        
        public IDisposable ViewTapGuard()
        {
            return this.Activate();
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnEscape();
            return true;
        }
        
        void IAsyncActivityControl.ActivityBegin()
        {
            SetInteractable(false);
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            SetInteractable(true);
        }
        
        void SetInteractable(bool interactable)
        {
            ActualView.UserInteraction = interactable;
        }

        [UIAction]
        void OnCloseButtonClicked()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
