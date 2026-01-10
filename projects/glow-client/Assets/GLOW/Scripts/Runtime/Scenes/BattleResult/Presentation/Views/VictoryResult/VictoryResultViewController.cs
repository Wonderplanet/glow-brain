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

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// 　　53-1-1_勝利画面
    /// 　　53-1-1-1_勝利演出
    /// 　　53-1-1-2_勝利演出時キャラ表示
    /// </summary>
    public class VictoryResultViewController : UIViewController<VictoryResultView>, IEscapeResponder
    {
        public record Argument(VictoryResultViewModel ViewModel, Action OnViewClosed, Func<UniTask> OnRetrySelected);

        [Inject] IVictoryResultViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.OnPlayerResourceIconTapped = ViewDelegate.OnIconSelected;
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void SetCharacterStandImage(CharacterStandImageAssetPath assetPath)
        {
            ActualView.SetCharacterStandImage(assetPath);
        }

        public void SetSpeedAttack(ResultSpeedAttackViewModel viewModel)
        {
            ActualView.SetSpeedAttack(viewModel.ClearTime, viewModel.SpeedAttackRewards);
        }

        public void SetAcquiredItems(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels)
        {
            ActualView.SetAcquiredItems(iconViewModels, groupedIconViewModels);
        }

        public async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlaySlideInAnimation(cancellationToken);
        }

        public async UniTask PlayUserExpGainAnimation(
            UserExpGainViewModel userExpGainViewModel,
            float duration,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayUserExpGainAnimation(userExpGainViewModel, duration, cancellationToken);
        }

        public async UniTask PlayExpandRewardListAnimation(bool isHiddenSpeedAttack, CancellationToken cancellationToken)
        {
            await ActualView.PlayExpandRewardListAnimation(isHiddenSpeedAttack, cancellationToken);
        }

        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayAcquiredItemsAnimation(iconViewModels, groupedIconViewModels, cancellationToken);
        }

        public async UniTask PlaySpeedAttackRewardListAnimation(
            IReadOnlyList<ResultSpeedAttackRewardViewModel> rewards,
            CancellationToken cancellationToken)
        {
            await ActualView.PlaySpeedAttackRewardListAnimation(rewards, cancellationToken);
        }

        public async UniTask PlaySpeedAttackResultAnimation(
            NewRecordFlag isNewRecord,
            StageClearTime clearTime,
            float duration,
            CancellationToken cancellationToken)
        {
            await ActualView.PlaySpeedAttackResultAnimation(isNewRecord, clearTime, duration, cancellationToken);
        }

        public void SkipSlideInAnimation()
        {
            ActualView.SkipSlideInAnimation();
        }

        public void SkipUserExpGainAnimation()
        {
            ActualView.SkipUserExpGainAnimation();
        }

        public void SkipSpeedAttackRewardListAnimation()
        {
            ActualView.SkipSpeedAttackRewardListAnimation();
        }

        public void SetInitialUserExp(UserExpGainViewModel userExpGainViewModel)
        {
            ActualView.SetInitialUserExp(userExpGainViewModel);
        }

        public void SetUserExpGain(UserExpGainViewModel userExpGainViewModel)
        {
            ActualView.SetUserExpGain(userExpGainViewModel);
        }

        public void ShowAcquiredItems(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels)
        {
            ActualView.SkipAcquiredItems(iconViewModels, groupedIconViewModels);
        }

        public void HideSkipScreenButton()
        {
            ActualView.HideSkipScreenButton();
        }

        public void ShowTapLabel(string text)
        {
            ActualView.ShowTapLabel(text);
        }

        public void HideTapLabel()
        {
            ActualView.HideTapLabel();
        }

        public void SetUpEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            ActualView.SetUpEventCampaignBalloon(remainingTimeSpan);
        }

        public void ShouldShowRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            ActualView.ShouldShowRetryButton(isRetryAvailable);
        }

        public void SetInteractableRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            ActualView.SetInteractableRetryButton(isRetryAvailable);
        }

        bool IEscapeResponder.OnEscape()
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
