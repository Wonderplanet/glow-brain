using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-6_リザルト画面
    /// </summary>
    public class FinishResultViewController : UIViewController<FinishResultView>, IEscapeResponder
    {
        public record Argument(FinishResultViewModel ViewModel, Action OnViewClosed, Func<UniTask> OnRetrySelected);

        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IFinishResultViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            ViewDelegate.OnViewDidLoad();
            ActualView.SetOnPlayerResourceIconTapped(ViewDelegate.OnIconSelected);
        }

        public override void UnloadView()
        {
            base.UnloadView();
            ViewDelegate.OnUnloadView();
        }

        public async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlaySlideInAnimation(cancellationToken);
        }

        public async UniTask IncreaseScoreAnimation(InGameScore currentScore, CancellationToken cancellationToken)
        {
            await ActualView.IncreaseScoreAnimation(currentScore, cancellationToken);
        }

        /// <summary> スコアアニメーション </summary>
        public async UniTask PlayScoreAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayScoreAnimation(cancellationToken);
        }

        /// <summary> ハイスコア獲得時のNewRecord表示と強調表示 </summary>
        public async UniTask PlayNewRecordAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayNewRecordAnimation(cancellationToken);
        }

        /// <summary> 報酬順次表示 </summary>
        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayAcquiredItemsAnimation(iconViewModels, cancellationToken);
        }

        /// <summary> 報酬即時表示 </summary>
        public void ShowAcquiredItems(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels)
        {
            ActualView.SetAcquiredItems(iconViewModels);
        }

        public void SkipSlideInAnimation()
        {
            ActualView.SkipSlideInAnimation();
        }

        public void SkipScoreAnimation()
        {
            ActualView.SkipScoreAnimation();
        }

        public void SkipNewRecordAnimation()
        {
            ActualView.SkipNewRecordAnimation();
        }

        public void SkipExpandRewardList()
        {
            ActualView.SkipExpandRewardList();
        }

        public void HideSkipScreenButton()
        {
            ActualView.HideSkipScreenButton();
        }

        public void ShowTapLabel(string text)
        {
            ActualView.ShowTapLabel(text);
        }

        /// <summary> 報酬倍率表示設定 </summary>
        public void SetRewardMultiplierText(EventBonusPercentage totalBonusPercentage)
        {
            ActualView.SetRewardMultiplierText(totalBonusPercentage);
        }

        /// <summary> スコア表示設定 </summary>
        public void SetScoreText(InGameScore currentScore)
        {
            ActualView.SetScoreText(currentScore);
        }

        /// <summary> ハイスコア、NewRecord表示設定 </summary>
        public void SetHighScoreText(InGameScore highScore, NewRecordFlag newRecordFlag)
        {
            ActualView.SetHighScoreText(highScore, newRecordFlag);
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

        public bool OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            SystemSoundEffectProvider.PlaySeTap();
            ViewDelegate.OnBackButton();
            return true;
        }

        [UIAction]
        void OnSkipTapped()
        {
            ViewDelegate.OnSkipSelected();
        }

        [UIAction]
        void OnCloseTapped()
        {
            ViewDelegate.OnCloseSelected();
        }

        [UIAction]
        void OnRetryTapped()
        {
            ViewDelegate.OnRetrySelected();
        }
    }
}
