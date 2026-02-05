using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.BattleResult.Presentation.Components.FinishResult;
using GLOW.Scenes.PvpBattleResult.Presentation.Component;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;
using GLOW.Scenes.PvpBattleResult.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public class PvpBattleResultView : UIView
    {
        [Header("Root")]
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] UIObject _resultRoot;
        [SerializeField] ScrollRect _resultContentScrollRect;
        [SerializeField] ScrollRectBarViewControl _resultContentScrollBarController;
        [SerializeField] CanvasGroup _resultContentScrollBarCanvasGroup;

        [Header("詳細スコア表示周り")]
        [SerializeField] UIObject _detailPointPanel;
        [SerializeField] AnimationPlayer _detailPointAnimationPlayer;
        [SerializeField] UIText _victoryPointText;
        [SerializeField] UIText _opponentBonusPointText;
        [SerializeField] UIText _timeBonusPointText;

        [Header("トータルスコア表示周り")]
        [SerializeField] UIObject _totalPointPanel;
        [SerializeField] AnimationPlayer _totalPointAnimationPlayer;
        [SerializeField] UIText _resultPointText;
        [SerializeField] UIText _totalPointText;
        [SerializeField] CanvasGroup _totalPointObjectCanvasGroup;
        [SerializeField] Animator _pointAnimator;

        [Header("ランク帯表示周り")]
        [SerializeField] PvpBattleResultRankPanelComponent _rankPanelComponent;

        [Header("閉じるボタン周り")]
        [SerializeField] FinishResultTapLabelComponent _closeTapLabelComponent;
        [SerializeField] UIObject _closeButtonObject;

        [Header("スキップボタン")]
        [SerializeField] UIObject _actionScreenButton;

        [Header("下の矢印")]
        [SerializeField] UIObject _arrowObject;
        [SerializeField] CanvasGroup _arrowObjectCanvasGroup;

        const float RankPanelNormalPosition = 0.0f;
        const float PanelAnimationInterval = 0.5f;
        const float ScrollAnimationDuration = 1.0f;

        public void InitializePvpResultUi()
        {
            _resultRoot.IsVisible = true;
            _resultContentScrollBarCanvasGroup.alpha = 0.0f;
            _resultContentScrollBarController.enabled = false;
            _totalPointObjectCanvasGroup.alpha = 0;

            _arrowObjectCanvasGroup.alpha = 0.0f;

            _resultPointText.SetText("0");

            _closeButtonObject.IsVisible = false;
        }

        public async UniTask PlayDetailPointSlideInAnimation(CancellationToken cancellationToken)
        {
            _timelineAnimation.Play();

            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

            _detailPointPanel.IsVisible = true;
            await _detailPointAnimationPlayer.PlayAsync(cancellationToken);
        }

        public async UniTask PlayDetailPointCountAnimation(
            CancellationToken cancellationToken,
            PvpPoint resultPoint,
            PvpPoint opponentBonusPoint,
            PvpPoint timeBonusPoint)
        {
            // NOTE: インゲーム対応完了後に値を繋ぎこみする

            var victoryPointCountTask = UniTask.Create(async () =>
            {
                long currentVictoryPointAnimationScore = 0;
                PlayScoreCountSe(currentVictoryPointAnimationScore);
                await DOTween.To(
                        () => currentVictoryPointAnimationScore,
                        x => currentVictoryPointAnimationScore = x,
                        resultPoint.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() => _victoryPointText.SetText(AmountFormatter.FormatAmount(currentVictoryPointAnimationScore)))
                    .WithCancellation(cancellationToken);
            });

            var opponentBonusPointCountTask = UniTask.Create(async () =>
            {
                long currentOpponentBonusPointAnimationScore = 0;
                await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken: cancellationToken);
                PlayScoreCountSe(opponentBonusPoint.Value);
                await DOTween.To(
                        () => currentOpponentBonusPointAnimationScore,
                        x => currentOpponentBonusPointAnimationScore = x,
                        opponentBonusPoint.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() => _opponentBonusPointText.SetText(
                        AmountFormatter.FormatAmount(currentOpponentBonusPointAnimationScore)))
                    .WithCancellation(cancellationToken);
            });

            var timeBonusPointCountTask = UniTask.Create(async () =>
            {
                long currentTimeBonusPointAnimationScore = 0;
                await UniTask.Delay(TimeSpan.FromSeconds(0.4f), cancellationToken: cancellationToken);
                PlayScoreCountSe(opponentBonusPoint.Value);
                await DOTween.To(
                        () => currentTimeBonusPointAnimationScore,
                        x => currentTimeBonusPointAnimationScore = x,
                        timeBonusPoint.Value,
                        0.4f)
                    .SetEase(Ease.Linear)
                    .OnUpdate(() => _timeBonusPointText.SetText(
                        AmountFormatter.FormatAmount(currentTimeBonusPointAnimationScore)))
                    .WithCancellation(cancellationToken);
            });

            await UniTask.WhenAll(victoryPointCountTask, opponentBonusPointCountTask, timeBonusPointCountTask);
            await UniTask.Delay(TimeSpan.FromSeconds(PanelAnimationInterval), cancellationToken: cancellationToken);
        }

        public async UniTask PlayTotalPointSlideInAnimation(CancellationToken cancellationToken)
        {
            _totalPointPanel.IsVisible = true;
            await _totalPointAnimationPlayer.PlayAsync(cancellationToken);
        }

        public async UniTask PlayTotalPointCountAnimation(
            CancellationToken cancellationToken,
            PvpPoint winAddPoint,
            PvpPoint totalPoint)
        {
            float duration = 1.0f;
            long currentAnimationPoint = 0;
            PlayScoreCountSe(winAddPoint.Value);
            await DOTween.To(
                    () => currentAnimationPoint,
                    x => currentAnimationPoint = x,
                    winAddPoint.Value,
                    duration)
                .SetEase(Ease.Linear)
                .OnUpdate(() => _resultPointText.SetText(AmountFormatter.FormatAmount(currentAnimationPoint)))
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);

            // スコアのズームアニメーション
            _pointAnimator.Play("Score");
            await UniTask.WaitUntil(
                () => _pointAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);

            bool notPlayTotalPointCountAnimation = totalPoint.IsZero() && winAddPoint.IsMinus();
            var beforePoint = PvpPoint.Zero;
            if (notPlayTotalPointCountAnimation)
            {
                _totalPointText.SetText("0");
            }
            else
            {
                // 反映前の累計スコアを設定
                beforePoint = PvpPoint.Max(totalPoint - winAddPoint, PvpPoint.Zero);
                _totalPointText.SetText(AmountFormatter.FormatAmount(beforePoint.Value));
            }

            // 累計スコアのin -> defアニメーション
            await _totalPointObjectCanvasGroup
                .DOFade(1.0f, 0.15f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);

            if (notPlayTotalPointCountAnimation)
            {
                return;
            }

            // 累計スコアのカウントアップアニメーション
            long currentTotalPointAnimationScore = beforePoint.Value;
            PlayScoreCountSe(winAddPoint.Value);
            await DOTween.To(
                    () => currentTotalPointAnimationScore,
                    x => currentTotalPointAnimationScore = x,
                    totalPoint.Value,
                    duration)
                .SetEase(Ease.Linear)
                .OnUpdate(() => _totalPointText.SetText(AmountFormatter.FormatAmount(currentTotalPointAnimationScore)))
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(PanelAnimationInterval), cancellationToken: cancellationToken);
        }

        public async UniTask PlayRankPanelAnimation(
            CancellationToken cancellationToken,
            PvpBattleResultPointViewModel viewModel)
        {
            // ランク帯のアニメーションを真ん中で再生させるようにスクロールさせる
            await _resultContentScrollRect.DOVerticalNormalizedPos(RankPanelNormalPosition, ScrollAnimationDuration)
                .SetEase(Ease.InOutExpo)
                .WithCancellation(cancellationToken);

            var firstTargetModel = viewModel.PvpResultPointRankTargetModels
                .FirstOrDefault(PvpBattleResultPointRankTargetViewModel.Empty);

            _rankPanelComponent.IsVisible = true;

            // ランクパネルのポイント表示部分の初期化
            _rankPanelComponent.InitializeRequiredPointArea(
                firstTargetModel.BeforePoint,
                firstTargetModel.TargetRankLowerRequiredPoint,
                viewModel.IsCurrentRankMaxLevel());

            if (firstTargetModel.IsEmpty())
            {
                // 空の場合は演出しない(ランクマッチを勝利で終えて空になるのは既に最大ランクに到達している場合、ポイント0で負けた時)
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentRankLevel,
                    PvpPoint.Empty,
                    PvpBattleResultRankAnimationGaugeRate.One);
            }
            else
            {
                var needRankUpScore = CalculateClampedRankUpScore(
                    firstTargetModel.TargetRankLowerRequiredPoint,
                    firstTargetModel.BeforePoint);
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentRankLevel,
                    needRankUpScore,
                    firstTargetModel.BeforeGaugeRate);
            }

            await _rankPanelComponent.PlayRankPanelAnimation(
                firstTargetModel.IsPointDecreased(),
                cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

            if (firstTargetModel.IsPointUpdated())
            {
                // スコアが更新されている場合はSEを鳴らす
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);
            }

            if (firstTargetModel.IsPointDecreased())
            {
                await PlayPointAndRankDownAnimation(cancellationToken, viewModel);
            }
            else
            {
                await PlayPointAndRankUpAnimation(cancellationToken, viewModel);
            }
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
            _resultContentScrollBarController.enabled = true;

            await _resultContentScrollBarCanvasGroup.DOFade(1.0f, 0.3f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);
        }

        public void SkipDetailPointSlideInAnimation()
        {
            _timelineAnimation.Skip();

            _detailPointPanel.IsVisible = true;
            _detailPointAnimationPlayer.Skip();
        }

        public void SkipDetailPointCountAnimation(
            PvpPoint resultPoint,
            PvpPoint opponentBonusPoint,
            PvpPoint timeBonusPoint)
        {
            _victoryPointText.SetText(AmountFormatter.FormatAmount(resultPoint.Value));
            _opponentBonusPointText.SetText(AmountFormatter.FormatAmount(opponentBonusPoint.Value));
            _timeBonusPointText.SetText(AmountFormatter.FormatAmount(timeBonusPoint.Value));
        }

        public void SkipTotalPointSlideInAnimation()
        {
            _totalPointAnimationPlayer.Skip();
        }

        public void SkipTotalPointCountAnimation(PvpPoint winAddPoint, PvpPoint totalPoint)
        {
            _resultPointText.SetText(AmountFormatter.FormatAmount(winAddPoint.Value));
            _totalPointText.SetText(AmountFormatter.FormatAmount(totalPoint.Value));
            _totalPointObjectCanvasGroup.alpha = 1.0f;
        }

        public void SkipRankPanelAnimation(PvpBattleResultPointViewModel viewModel)
        {
            _resultContentScrollRect.verticalNormalizedPosition = RankPanelNormalPosition;

            _rankPanelComponent.IsVisible = true;
            bool isRankDown = false;
            if (viewModel.PvpResultPointRankTargetModels.IsEmpty())
            {
                _rankPanelComponent.SetupRankIcon(
                    viewModel.CurrentRankType,
                    viewModel.CurrentRankLevel,
                    PvpPoint.Empty,
                    viewModel.FillRate());
            }
            else
            {
                var lastAchievedRankAndLevel = viewModel.LastAchievedRankAndLevel();
                var targetModel = viewModel.PvpResultPointRankTargetModels.Last();

                PvpPoint pointToNextRank;
                pointToNextRank = CalculateClampedRankUpScore(
                    targetModel.TargetRankLowerRequiredPoint,
                    targetModel.AfterPoint);
                if (targetModel.BeforePoint > targetModel.AfterPoint) isRankDown = true;

                _rankPanelComponent.SetupRankIcon(
                    lastAchievedRankAndLevel.rankType,
                    lastAchievedRankAndLevel.rankLevel,
                    pointToNextRank,
                    targetModel.AfterGaugeRate);
            }

            _rankPanelComponent.SkipRankPanelAnimation(isRankDown);
        }

        public void HideCloseTapLabel()
        {
            _closeTapLabelComponent.Hide();
        }

        public void ShowActionButton()
        {
            _actionScreenButton.IsVisible = true;
        }

        public void HideActionButton()
        {
            _actionScreenButton.IsVisible = false;
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

        public void SkipScrollBarFadeAnimation()
        {
            _resultContentScrollBarController.enabled = true;
            _resultContentScrollBarCanvasGroup.alpha = 1.0f;
        }

        void PlayScoreCountSe(long point)
        {
            if (point != 0)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_008);
            }
        }

        async UniTask PlayPointAndRankUpAnimation(
            CancellationToken cancellationToken,
            PvpBattleResultPointViewModel viewModel)
        {
            PvpRankClassType currentRankType = viewModel.CurrentRankType;

            foreach (var targetModel in viewModel.PvpResultPointRankTargetModels)
            {
                await PlayCountAndGaugeAnimation(targetModel, cancellationToken);

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

        async UniTask PlayPointAndRankDownAnimation(
            CancellationToken cancellationToken,
            PvpBattleResultPointViewModel viewModel)
        {
            // 最大ランクの状態からランクダウンしない場合は表示が変わらないのでスキップ
            if (viewModel.IsKeepRankMaxLevel()) return;
            
            PvpRankClassType currentRankType = viewModel.CurrentRankType;

            for (var i = 0; i < viewModel.PvpResultPointRankTargetModels.Count; i++)
            {
                var targetModel = viewModel.PvpResultPointRankTargetModels[i];

                await PlayCountAndGaugeAnimation(targetModel, cancellationToken);

                if (i != viewModel.PvpResultPointRankTargetModels.Count - 1)
                {
                    if (currentRankType != targetModel.TargetRankType)
                    {
                        SoundEffectPlayer.Play(SoundEffectId.SSE_043_006);

                        _rankPanelComponent.PlayChangeRankIcon(
                            targetModel.TargetRankType,
                            cancellationToken
                        ).Forget();

                        currentRankType = targetModel.TargetRankType;
                    }

                    _rankPanelComponent.SetupRankName(
                        targetModel.TargetRankType);

                    _rankPanelComponent.PlayRankDowngradeAnimation(targetModel.TargetScoreRankLevel);
                }
            }
        }

        async UniTask PlayCountAndGaugeAnimation(
            PvpBattleResultPointRankTargetViewModel targetModel,
            CancellationToken cancellationToken)
        {
            var pointCountAnimation = _rankPanelComponent.PlayPointCountAnimation(
                targetModel.BeforePoint,
                targetModel.AfterPoint,
                targetModel.TargetRankLowerRequiredPoint,
                targetModel.IsTargetMaxRankLevel() && targetModel.IsAchievedRank(),
                cancellationToken);

            var gaugeAnimation = _rankPanelComponent.PlayPointGaugeAnimation(
                targetModel.BeforeGaugeRate,
                targetModel.AfterGaugeRate,
                targetModel.IsAchievedRank(),
                targetModel.IsTargetMaxRankLevel() && targetModel.IsAchievedRank(),
                cancellationToken);

            await UniTask.WhenAll(pointCountAnimation, gaugeAnimation);
        }

        PvpPoint CalculateClampedRankUpScore(
            PvpPoint targetRequiredLowerPoint,
            PvpPoint beforePoint)
        {
            if (targetRequiredLowerPoint.IsEmpty())
            {
                return PvpPoint.Zero;
            }

            var subScore = targetRequiredLowerPoint - beforePoint;
            if (subScore < PvpPoint.Zero)
            {
                // ランクアップに必要なスコアが0未満の場合は0にする
                subScore = PvpPoint.Zero;
            }
            return subScore;
        }
    }
}
