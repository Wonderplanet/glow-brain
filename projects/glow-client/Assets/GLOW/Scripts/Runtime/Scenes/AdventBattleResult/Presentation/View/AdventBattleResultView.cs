using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleResult.Presentation.Component;
using GLOW.Scenes.AdventBattleResult.Presentation.ValueObject;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;
using GLOW.Scenes.BattleResult.Presentation.Components.FinishResult;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultView : UIView
    {
        [Header("Root")]
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] UIObject _resultRoot;
        [SerializeField] ScrollRect _resultContentScrollRect;
        [SerializeField] ScrollRectBarViewControl _resultContentScrollBarController;
        [SerializeField] CanvasGroup _resultContentScrollBarCanvasGroup;

        [Header("詳細スコア表示周り")]
        [SerializeField] UIObject _detailScorePanel;
        [SerializeField] AnimationPlayer _detailScoreAnimationPlayer;
        [SerializeField] UIText _totalDamageText;
        [SerializeField] UIText _defeatEnemyScoreText;
        [SerializeField] UIText _defeatBossEnemyScoreText;

        [Header("トータルスコア表示周り")]
        [SerializeField] UIObject _totalScorePanel;
        [SerializeField] AnimationPlayer _totalScoreAnimationPlayer;
        [SerializeField] UIText _resultScoreText;
        [SerializeField] UIText _highScoreText;
        [SerializeField] CanvasGroup _highScoreObjectCanvasGroup;
        [SerializeField] UIObject _newRecordObject;
        [SerializeField] Animator _scoreAnimator;
        [SerializeField] Animator _newRecordAnimator;

        [Header("ランク帯表示周り")]
        [SerializeField] AdventBattleResultRankPanelComponent _rankPanelComponent;

        [Header("報酬リスト表示周り")]
        [SerializeField] UIObject _rewardListPanel;
        [SerializeField] UIObject _rewardListPanelDecoObject;
        [SerializeField] AnimationPlayer _rewardListExpandAnimationPlayer;
        [SerializeField] PlayerResourceIconList _iconList;
        [SerializeField] UIText _nonAcquiredText;

        [Header("閉じるボタン周り")]
        [SerializeField] FinishResultTapLabelComponent _closeTapLabelComponent;
        [SerializeField] UIObject _closeButtonObject;

        [Header("スキップボタン")]
        [SerializeField] UIObject _actionScreenButton;

        [Header("下の矢印")]
        [SerializeField] UIObject _arrowObject;
        [SerializeField] CanvasGroup _arrowObjectCanvasGroup;

        [Header("キャンペーン")]
        [SerializeField] EventCampaignBalloon _eventCampaignBalloon;
        
        [Header("再挑戦ボタン")]
        [SerializeField] UIObject _retryRoot;
        [SerializeField] Button _retryButton;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped
        {
            get => _iconList.OnPlayerResourceIconTapped;
            set
            {
                _iconList.OnPlayerResourceIconTapped = value;
            }
        }

        const float RankPanelNormalPosition = 0.62f;
        const float ScrollAnimationDuration = 1.0f;
        const float PanelAnimationInterval = 0.5f;

        protected override void Awake()
        {
            base.Awake();
            
            // 誤操作防止のため初期状態でinteractableをfalseにしておく
            _retryButton.interactable = false;
        }

        public async UniTask PlayDetailScoreSlideInAnimation(CancellationToken cancellationToken)
        {
            _resultRoot.IsVisible = true;
            _resultContentScrollBarCanvasGroup.alpha = 0.0f;
            _resultContentScrollBarController.enabled = false;
            _newRecordObject.IsVisible = false;
            _highScoreObjectCanvasGroup.alpha = 0;

            _arrowObjectCanvasGroup.alpha = 0.0f;

            _closeButtonObject.IsVisible = false;
            
            _resultScoreText.SetText("0");

            _timelineAnimation.Play();

            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

            _detailScorePanel.IsVisible = true;
            await _detailScoreAnimationPlayer.PlayAsync(cancellationToken);
        }

        public async UniTask PlayDetailScoreCountAnimation(
            AdventBattleScore damageScore,
            AdventBattleScore defeatEnemyScore,
            AdventBattleScore defeatBossEnemyScore,
            CancellationToken cancellationToken)
        {
            // NOTE: インゲーム対応完了後に値を繋ぎこみする

            var totalDamageCountTask = UniTask.Create(async () =>
            {
                long currentDamageAnimationScore = 0;
                PlayScoreCountSe(damageScore);
                await DOTween.To(
                        () => currentDamageAnimationScore,
                        x => currentDamageAnimationScore = x,
                        damageScore.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() => _totalDamageText.SetText(AmountFormatter.FormatAmount(currentDamageAnimationScore)))
                    .WithCancellation(cancellationToken);
                _totalDamageText.SetText(AmountFormatter.FormatAmount(damageScore.Value));
            });

            var defeatEnemyCountTask = UniTask.Create(async () =>
            {
                long currentDefeatEnemyAnimationScore = 0;
                await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken: cancellationToken);
                PlayScoreCountSe(defeatEnemyScore);
                await DOTween.To(
                        () => currentDefeatEnemyAnimationScore,
                        x => currentDefeatEnemyAnimationScore = x,
                        defeatEnemyScore.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() =>
                    {
                        _defeatEnemyScoreText.SetText(
                            AmountFormatter.FormatAmount(currentDefeatEnemyAnimationScore));
                    })
                    .WithCancellation(cancellationToken);
                _defeatEnemyScoreText.SetText(AmountFormatter.FormatAmount(defeatEnemyScore.Value));
            });

            var defeatBossEnemyCountTask = UniTask.Create(async () =>
            {
                long currentDefeatBossEnemyAnimationScore = 0;
                await UniTask.Delay(TimeSpan.FromSeconds(0.4f), cancellationToken: cancellationToken);
                PlayScoreCountSe(defeatBossEnemyScore);
                await DOTween.To(
                        () => currentDefeatBossEnemyAnimationScore,
                        x => currentDefeatBossEnemyAnimationScore = x,
                        defeatBossEnemyScore.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() =>
                    {
                        _defeatBossEnemyScoreText.SetText(
                            AmountFormatter.FormatAmount(currentDefeatBossEnemyAnimationScore));
                    })
                    .WithCancellation(cancellationToken);
                _defeatBossEnemyScoreText.SetText(AmountFormatter.FormatAmount(defeatBossEnemyScore.Value));
            });

            await UniTask.WhenAll(totalDamageCountTask, defeatEnemyCountTask, defeatBossEnemyCountTask);
            await UniTask.Delay(TimeSpan.FromSeconds(PanelAnimationInterval), cancellationToken: cancellationToken);
        }

        public async UniTask PlayTotalScoreSlideInAnimation(CancellationToken cancellationToken)
        {
            _totalScorePanel.IsVisible = true;
            await _totalScoreAnimationPlayer.PlayAsync(cancellationToken);
        }

        public async UniTask PlayTotalScoreCountAnimation(
            AdventBattleScore resultScore,
            AdventBattleScore highScore,
            NewRecordFlag isNewScore,
            CancellationToken cancellationToken)
        {
            _highScoreText.SetText(AmountFormatter.FormatAmount(highScore.Value));

            float duration = 1.0f;
            long currentAnimationScore = 0;
            PlayScoreCountSe(resultScore);
            await DOTween.To(
                    () => currentAnimationScore,
                    x => currentAnimationScore = x,
                    resultScore.Value,
                    duration)
                .SetEase(Ease.Linear)
                .OnUpdate(() => _resultScoreText.SetText(AmountFormatter.FormatAmount(currentAnimationScore)))
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);
            _resultScoreText.SetText(AmountFormatter.FormatAmount(resultScore.Value));

            // スコアのズームアニメーション
            _scoreAnimator.Play("Score");
            await UniTask.WaitUntil(
                () => _scoreAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);

            // ハイスコアのin -> defアニメーション
            await _highScoreObjectCanvasGroup
                .DOFade(1.0f, 0.15f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);

            if (isNewScore)
            {
                _newRecordObject.IsVisible = true;
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_010);
                _newRecordAnimator.Play("HighScore");
                await UniTask.WaitUntil(
                    () => _newRecordAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 0.5f,
                    cancellationToken:cancellationToken);
            }

            await UniTask.Delay(TimeSpan.FromSeconds(PanelAnimationInterval), cancellationToken: cancellationToken);
        }

        public async UniTask PlayRankPanelAnimation(
            AdventBattleResultScoreViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var firstTargetModel = viewModel.AdventBattleResultScoreRankTargetModels
                .FirstOrDefault(AdventBattleResultScoreRankTargetViewModel.Empty);

            // ランク帯のアニメーションを真ん中で再生させるようにスクロールさせる
            await _resultContentScrollRect.DOVerticalNormalizedPos(RankPanelNormalPosition, ScrollAnimationDuration)
                .SetEase(Ease.InOutExpo)
                .WithCancellation(cancellationToken);

            _rankPanelComponent.IsVisible = true;
            if (firstTargetModel.IsEmpty())
            {
                // 空の場合は演出しない(降臨バトルを勝利で終えて空になるのは既に最大ランクに到達している場合)
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentScoreRankLevel,
                    AdventBattleScore.Empty,
                    AdventBattleResultRankAnimationGaugeRate.One);
            }
            else
            {
                var needRankUpScore = firstTargetModel.TargetRankLowerRequiredScore - firstTargetModel.BeforeTotalScore;
                if (needRankUpScore < AdventBattleScore.Zero)
                {
                    // ランクアップに必要なスコアが0未満の場合は0にする
                    needRankUpScore = AdventBattleScore.Zero;
                }
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentScoreRankLevel,
                    needRankUpScore,
                    firstTargetModel.BeforeGaugeRate);
            }

            await _rankPanelComponent.PlayRankPanelAnimation(cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

            RankType currentRankType = viewModel.CurrentRankType;

            if (viewModel.IsScoreUpdated())
            {
                // スコアが更新されている場合はSEを鳴らす
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);
            }

            foreach (var targetModel in viewModel.AdventBattleResultScoreRankTargetModels)
            {
                var scoreCountAnimation = _rankPanelComponent.PlayScoreCountAnimation(
                    targetModel.BeforeTotalScore,
                    targetModel.AfterTotalScore,
                    targetModel.TargetRankLowerRequiredScore,
                    targetModel.IsTargetMaxRankLevel(),
                    cancellationToken);

                var gaugeAnimation = _rankPanelComponent.PlayScoreGaugeAnimation(
                    targetModel.AfterGaugeRate,
                    targetModel.IsAchievedRank(),
                    targetModel.IsTargetMaxRankLevel(),
                    cancellationToken);

                await UniTask.WhenAll(scoreCountAnimation, gaugeAnimation);

                if (targetModel.IsAchievedRank())
                {
                    if (currentRankType != targetModel.TargetRankType)
                    {
                        SoundEffectPlayer.Play(SoundEffectId.SSE_053_002);

                        _rankPanelComponent.PlayChangeRankIcon(
                            targetModel.TargetRankType,
                            cancellationToken
                        ).Forget();

                        currentRankType = targetModel.TargetRankType;
                    }

                    _rankPanelComponent.SetupRankName(
                        targetModel.TargetRankType);

                    await _rankPanelComponent.PlayRankChangeAnimation(
                        targetModel.TargetScoreRankLevel,
                        cancellationToken);
                }
            }
        }

        public async UniTask PlayRewardPanelAnimation(
            CancellationToken cancellationToken)
        {
            await _resultContentScrollRect.DOVerticalNormalizedPos(0.0f, ScrollAnimationDuration)
                .SetEase(Ease.InOutExpo)
                .WithCancellation(cancellationToken);

            _rewardListPanel.IsVisible = true;
            _rewardListPanelDecoObject.IsVisible = false;
            await _rewardListExpandAnimationPlayer.PlayAsync(cancellationToken);
        }

        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels,
            CancellationToken cancellationToken)
        {
            if (rewardViewModels.Count <= 0)
            {
                _nonAcquiredText.IsVisible = true;
                return;
            }

            var isCellAnimationCompleted = false;
            _iconList.SetupAndReload(
                rewardViewModels,
                true,
                1,
                onComplete:() => isCellAnimationCompleted = true);

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }

        public void PlayCloseTextFadeAnimation()
        {
            _closeButtonObject.IsVisible = true;
            _closeTapLabelComponent.Show("タップで閉じる");
        }

        public async UniTask PlayArrowFadeInAnimation(CancellationToken cancellationToken)
        {
            _arrowObject.IsVisible = true;

            await _arrowObjectCanvasGroup
                .DOFade(1.0f, 0.3f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);
        }

        public async UniTask PlayArrowFadeOutAnimation(CancellationToken cancellationToken)
        {
            await _arrowObjectCanvasGroup
                .DOFade(0.0f, 0.3f)
                .SetEase(Ease.Linear)
                .OnComplete(() => _arrowObject.IsVisible = false)
                .WithCancellation(cancellationToken);
        }

        public async UniTask PlayScrollBarFadeAnimation(CancellationToken cancellationToken)
        {
            await _resultContentScrollBarCanvasGroup.DOFade(1.0f, 0.3f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(4f), cancellationToken: cancellationToken);

            _resultContentScrollBarController.enabled = true;
        }

        public void HiddenCloseTapLabel()
        {
            _closeTapLabelComponent.Hide();
        }

        public void SkipDetailScoreSlideInAnimation()
        {
            _timelineAnimation.Skip();

            _detailScorePanel.IsVisible = true;
            _detailScoreAnimationPlayer.Skip();
        }

        public void SkipDetailScoreCountAnimation(
            AdventBattleScore damageScore,
            AdventBattleScore defeatEnemyScore,
            AdventBattleScore defeatBossEnemyScore)
        {
            // NOTE: インゲーム対応完了後に値を繋ぎこみする

            _totalDamageText.SetText(AmountFormatter.FormatAmount(damageScore.Value));
            _defeatEnemyScoreText.SetText(AmountFormatter.FormatAmount(defeatEnemyScore.Value));
            _defeatBossEnemyScoreText.SetText(AmountFormatter.FormatAmount(defeatBossEnemyScore.Value));
        }

        public void SkipTotalScoreSlideInAnimation()
        {
            _totalScoreAnimationPlayer.Skip();
        }

        public void SkipTotalScoreCountAnimation(
            AdventBattleScore resultScore,
            AdventBattleScore highScore,
            NewRecordFlag isNewScore)
        {
            _resultScoreText.SetText(AmountFormatter.FormatAmount(resultScore.Value));

            // スコアのズームアニメーション
            _scoreAnimator.Play("Score", 0, 1);

            // ハイスコアのin -> defアニメーション
            _highScoreText.SetText(AmountFormatter.FormatAmount(highScore.Value));
            _highScoreObjectCanvasGroup.alpha = 1.0f;

            if (isNewScore)
            {
                _newRecordObject.IsVisible = true;
                _newRecordAnimator.Play("HighScore", 0, 1);
            }
        }

        public void SkipRewardPanelAnimation()
        {
            _rewardListExpandAnimationPlayer.Skip();
        }

        public void SkipRankPanelAnimation(AdventBattleResultScoreViewModel viewModel)
        {
            _resultContentScrollRect.verticalNormalizedPosition = RankPanelNormalPosition;

            _rankPanelComponent.IsVisible = true;
            if (viewModel.AdventBattleResultScoreRankTargetModels.IsEmpty())
            {
                // 空の場合は演出しない(降臨バトルを勝利で終えて空になるのは既に最大ランクに到達している場合)
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentScoreRankLevel,
                    AdventBattleScore.Empty,
                    AdventBattleResultRankAnimationGaugeRate.One);
            }
            else
            {
                var lastAchievedRankAndLevel = viewModel.LastAchievedRankAndLevel();
                var targetModel = viewModel.AdventBattleResultScoreRankTargetModels.Last();
                var needRankUpScore = targetModel.TargetRankLowerRequiredScore - targetModel.AfterTotalScore;
                if (needRankUpScore < AdventBattleScore.Zero)
                {
                    // ランクアップに必要なスコアが0未満の場合は0にする
                    needRankUpScore = AdventBattleScore.Zero;
                }
                _rankPanelComponent.SetupRankIcon(
                    lastAchievedRankAndLevel.rankType,
                    lastAchievedRankAndLevel.rankLevel,
                    needRankUpScore,
                    targetModel.AfterGaugeRate);
            }

            _rankPanelComponent.SkipRankPanelAnimation();
        }

        public void SkipAcquiredItemsAnimation(IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels)
        {
            _resultContentScrollRect.verticalNormalizedPosition = 0.0f;
            _iconList.IsVisible = true;
            _iconList.PlayerResourceIconAnimation?.SkipAnimation();

            // 0の時はonCompleteが呼ばれず待ちが終わらないため処理を飛ばす
            if (rewardViewModels.Count <= 0)
            {
                return;
            }

            _iconList.SetupAndReload(rewardViewModels, false);
        }

        public void SkipScrollBarFadeAnimation()
        {
            _resultContentScrollBarCanvasGroup.alpha = 1.0f;
            _resultContentScrollBarController.enabled = true;
        }

        public void SkipArrowFadeInAnimation()
        {
            _arrowObjectCanvasGroup.alpha = 1.0f;
            _arrowObject.IsVisible = true;
        }

        public void SkipArrowFadeOutAnimation()
        {
            _arrowObjectCanvasGroup.alpha = 0.0f;
            _arrowObject.IsVisible = false;
        }

        public void ShowActionButton()
        {
            _actionScreenButton.IsVisible = true;
        }

        public void HideActionButton()
        {
            _actionScreenButton.IsVisible = false;
        }

        void PlayScoreCountSe(AdventBattleScore score)
        {
            if (!score.IsZero())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_008);
            }
        }

        public void SetUpEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            _eventCampaignBalloon.gameObject.SetActive(remainingTimeSpan.HasValue());
            _eventCampaignBalloon.SetRemainingTimeText(remainingTimeSpan);
        }
        
        public void SetupRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            // アニメーション再生前に表示設定
            _retryRoot.IsVisible = isRetryAvailable;
        }

        public void SetActiveRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            // アニメーション完了後にinteractableを設定
            _retryButton.interactable = isRetryAvailable;
        }
    }
}
