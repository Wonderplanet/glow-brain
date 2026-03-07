using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultViewController : UIViewController<AdventBattleResultView>
    {
        public record Argument(AdventBattleResultViewModel ViewModel);
        public Action OnCloseAction { get; set; }
        public Func<UniTask> OnRetryAction { get; set; }

        [Inject] IAdventBattleResultViewDelegate AdventBattleResultViewDelegate { get; }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            AdventBattleResultViewDelegate.OnViewDidAppear();
            ActualView.OnPlayerResourceIconTapped = AdventBattleResultViewDelegate.OnIconSelected;
        }

        public override void UnloadView()
        {
            base.UnloadView();
            AdventBattleResultViewDelegate.OnUnloadView();
        }

        public async UniTask PlayDetailScoreSlideInAnimation(
            AdventBattleScore damageScore,
            AdventBattleScore defeatEnemyScore,
            AdventBattleScore defeatBossEnemyScore,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayDetailScoreSlideInAnimation(cancellationToken);
            await ActualView.PlayDetailScoreCountAnimation(
                damageScore,
                defeatEnemyScore,
                defeatBossEnemyScore,
                cancellationToken);
            
            await ActualView.PlayArrowFadeInAnimation(cancellationToken);
        }

        public async UniTask PlayTotalScoreSlideInAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayTotalScoreSlideInAnimation(cancellationToken);
        }

        public async UniTask PlayTotalScoreCountAnimation(
            AdventBattleResultViewModel viewModel, 
            CancellationToken cancellationToken)
        {
            await ActualView.PlayTotalScoreCountAnimation(
                viewModel.CurrentScore,
                viewModel.HighScore,
                viewModel.NewRecordFlag,
                cancellationToken);
        }

        public async UniTask PlayRewardAnimation(AdventBattleResultViewModel viewModel, CancellationToken cancellationToken)
        {
            await ActualView.PlayRewardPanelAnimation(cancellationToken);

            await ActualView.PlayAcquiredItemsAnimation(viewModel.AcquiredPlayerResources, cancellationToken);

            ActualView.PlayCloseTextFadeAnimation();

            ActualView.PlayArrowFadeOutAnimation(cancellationToken).Forget();;

            ActualView.PlayScrollBarFadeAnimation(cancellationToken).Forget();
        }

        public async UniTask PlayRankPanelAnimation(
            AdventBattleResultScoreViewModel viewModel, 
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRankPanelAnimation(viewModel, cancellationToken);
        }

        public void SkipDetailScoreAnimation(
            AdventBattleScore damageScore,
            AdventBattleScore defeatEnemyScore,
            AdventBattleScore defeatBossEnemyScore)
        {
            ActualView.SkipDetailScoreSlideInAnimation();

            ActualView.SkipDetailScoreCountAnimation(damageScore, defeatEnemyScore, defeatBossEnemyScore);
            
            ActualView.SkipArrowFadeInAnimation();
        }

        public void SkipTotalScoreSlideInAnimation()
        {
            ActualView.SkipTotalScoreSlideInAnimation();
        }

        public void SkipTotalScoreCountAnimation(AdventBattleResultViewModel viewModel)
        {
            ActualView.SkipTotalScoreCountAnimation(
                viewModel.CurrentScore,
                viewModel.HighScore,
                viewModel.NewRecordFlag);
        }

        public void SkipRewardAnimation(AdventBattleResultViewModel viewModel)
        {
            ActualView.SkipRewardPanelAnimation();

            ActualView.SkipAcquiredItemsAnimation(viewModel.AcquiredPlayerResources);

            ActualView.SkipScrollBarFadeAnimation();

            ActualView.SkipArrowFadeOutAnimation();

            ActualView.PlayCloseTextFadeAnimation();
        }

        public void SkipRankPanelAnimation(AdventBattleResultScoreViewModel viewModel)
        {
            ActualView.SkipRankPanelAnimation(viewModel);
        }

        public void ShowActionButton()
        {
            ActualView.ShowActionButton();
        }

        public void HideActionButton()
        {
            ActualView.HideActionButton();
        }

        public void HideCloseText()
        {
            ActualView.HiddenCloseTapLabel();
        }

        public void SetUpEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            ActualView.SetUpEventCampaignBalloon(remainingTimeSpan);
        }

        public void SetupRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            ActualView.SetupRetryButton(isRetryAvailable);
        }

        public void SetActiveRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            ActualView.SetActiveRetryButton(isRetryAvailable);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            AdventBattleResultViewDelegate.OnCloseButtonTapped();
            OnCloseAction?.Invoke();
        }

        [UIAction]
        void OnActionButtonTapped()
        {
            AdventBattleResultViewDelegate.OnActionButtonTapped();
        }

        [UIAction]
        void OnRetryButtonTapped()
        {
            AdventBattleResultViewDelegate.OnRetryButtonTapped();
        }
    }
}
