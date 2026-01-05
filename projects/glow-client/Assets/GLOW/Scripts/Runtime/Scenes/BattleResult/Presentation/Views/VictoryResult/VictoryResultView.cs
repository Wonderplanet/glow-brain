using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// 　　53-1-1_勝利画面
    /// 　　53-1-1-1_勝利演出
    /// 　　53-1-1-2_勝利演出時キャラ表示
    /// </summary>
    public class VictoryResultView : UIView
    {
        [SerializeField] GameObject _resultRoot;
        [SerializeField] VictoryResultAnimationController _animationController;
        [SerializeField] UIImage _characterStandImage;
        [SerializeField] VictoryResultUserLevelComponent _userLevelComponent;
        [SerializeField] UIText _requiredExpText;
        [SerializeField] Image _userExpGaugeImage;
        [SerializeField] VictoryResultLevelUpComponent _levelUpComponent;
        [SerializeField] UIText _noAcquiredText;
        [SerializeField] VictoryRewardComponent _rewardComponent;
        [SerializeField] VictoryResultSpeedAttackRewardList _speedAttackRewardList;
        [SerializeField] ScrollRect _rewardList;
        [SerializeField] GameObject _skipScreenButton;
        [SerializeField] VictoryResultTapLabelComponent _tapLabel;
        [SerializeField] VictoryResultSpeedAttackComponent _speedAttackComponent;
        [SerializeField] EventCampaignBalloon _eventCampaignBalloon;
        [SerializeField] GameObject _retryObject;
        [SerializeField] Button _retryButton;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        protected override void Awake()
        {
            base.Awake();

            _resultRoot.SetActive(false);
            _skipScreenButton.SetActive(true);
            _rewardComponent.Hidden = true;
            _speedAttackRewardList.Hidden = true;
            _noAcquiredText.Hidden = true;
            _speedAttackComponent.Hidden = true;

            // 誤操作防止のため初期状態でinteractableをfalseにしておく
            _retryButton.interactable = false;

            // この記述で常に最新のOnPlayerResourceIconTappedを叩くようにする
            //  " = OnPlayerResourceIconTapped; "だとタイミング問題でnullが入る可能性ある
            _rewardComponent.OnPlayerResourceIconTapped = (vm) => OnPlayerResourceIconTapped?.Invoke(vm);
        }

        public void SetCharacterStandImage(CharacterStandImageAssetPath assetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_characterStandImage.Image, assetPath.Value);
        }

        public void SetSpeedAttack(StageClearTime clearTime, IReadOnlyList<ResultSpeedAttackRewardViewModel> rewards)
        {
            _speedAttackComponent.Hidden = clearTime.IsEmpty();
            _speedAttackComponent.HiddenNewRecord();

            _speedAttackRewardList.Setup(rewards);
        }

        public void SetAcquiredItems(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels)
        {
            _rewardComponent.Setup(
                iconViewModels,
                groupedIconViewModels);
        }

        public async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            _resultRoot.SetActive(true);
            await _animationController.SlideIn(cancellationToken);
        }

        public async UniTask PlayUserExpGainAnimation(
            UserExpGainViewModel userExpGainViewModel,
            float duration,
            CancellationToken cancellationToken)
        {
            // ユーザーレベル
            _userLevelComponent.SetLevel(userExpGainViewModel.Level, userExpGainViewModel.IsLevelUp);

            // レベルアップ演出
            if (userExpGainViewModel.IsLevelUp)
            {
                _levelUpComponent.Play();
            }

            // レベルアップまでの必要経験値を減少させる演出
            var startRequiredExp = userExpGainViewModel.NextLevelExp - userExpGainViewModel.StartExp;
            var endRequiredExp = userExpGainViewModel.NextLevelExp - userExpGainViewModel.EndExp;

            _requiredExpText.SetText(endRequiredExp.ToString());

            long exp = startRequiredExp.Value;
            var expTextTween = DOTween.To(
                    () => exp,
                    value => exp = value,
                    endRequiredExp.Value,
                    duration)
                .OnUpdate(() => _requiredExpText.SetText(new RelativeUserExp(exp).ToString()))
                .ToUniTask(TweenCancelBehaviour.Complete, cancellationToken);

            // 経験値ゲージを増加させる演出
            var startExpRatio = !userExpGainViewModel.NextLevelExp.IsZero()
                ? userExpGainViewModel.StartExp / userExpGainViewModel.NextLevelExp
                : 1f;

            var endExpRatio = !userExpGainViewModel.NextLevelExp.IsZero()
                ? userExpGainViewModel.EndExp / userExpGainViewModel.NextLevelExp
                : 1f;

            _userExpGaugeImage.fillAmount = startExpRatio;

            var expGaugeTween = DOTween.To(
                    () => _userExpGaugeImage.fillAmount,
                    value => _userExpGaugeImage.fillAmount = value,
                    endExpRatio,
                    duration)
                .ToUniTask(TweenCancelBehaviour.Complete, cancellationToken);

            // 演出が終わるまで待つ
            await UniTask.WhenAll(expTextTween, expGaugeTween);
        }

        public async UniTask PlaySpeedAttackRewardListAnimation(
            IReadOnlyList<ResultSpeedAttackRewardViewModel> rewards,
            CancellationToken cancellationToken)
        {
            if (_speedAttackRewardList.Hidden) return;

            var content = _rewardList.content;
            var cellCount = rewards.Count;
            var scrollDuration = 0.5f / cellCount;
            var scrolledHeight = 0f;

            if (cellCount < 3)
            {
                // スタンプ全部処理してからまとめてスクロール
                for (int i = 0; i < cellCount; ++i)
                {
                    await _speedAttackRewardList.PlayCellClearStampAsync(i, cancellationToken);
                }
            }
            else
            {
                // １つずつスタンプとスクロールをする
                for(int i = 0; i < cellCount; ++i)
                {
                    if (0 < i)
                    {
                        var cellHeight = _speedAttackRewardList.GetCellHeight();
                        scrolledHeight += cellHeight;
                        var position = content.localPosition;
                        await DOTween.To(
                                () => position.y,
                                value => content.localPosition = new Vector2(position.x, value),
                                position.y + cellHeight,
                                scrollDuration)
                            .ToUniTask(TweenCancelBehaviour.CompleteAndCancelAwait, cancellationToken);
                    }
                    await _speedAttackRewardList.PlayCellClearStampAsync(i, cancellationToken);
                }

                var scrollPosition = content.localPosition;
                await DOTween.To(
                        () => scrollPosition.y,
                        value => content.localPosition = new Vector2(scrollPosition.x, value),
                        scrollPosition.y + _speedAttackRewardList.GetHeight() - scrolledHeight,
                        scrollDuration)
                    .ToUniTask(TweenCancelBehaviour.CompleteAndCancelAwait, cancellationToken);
            }
        }

        public void SkipSpeedAttackRewardListAnimation()
        {
            _speedAttackRewardList.SetClearStamp();

            // スクロールをリセットする
            _rewardList.normalizedPosition = Vector2.up;
        }

        public async UniTask PlayExpandRewardListAnimation(bool isHiddenSpeedAttack, CancellationToken cancellationToken)
        {
            _speedAttackRewardList.Hidden = isHiddenSpeedAttack;
            _rewardComponent.Hidden = false;
            await UpdateCanvases(cancellationToken);

            await _animationController.ExpandRewardList(cancellationToken);
        }

        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels,
            CancellationToken cancellationToken)
        {
            _noAcquiredText.Hidden = 1 <= iconViewModels.Count;
            var isCellAnimationCompleted = false;

            // セルアニメーション再生
            _rewardComponent.PlayRewardCellAnimation();

            // スクロール処理
            var content = _rewardList.content;
            var startPosition = content.localPosition;
            var contentHeight = content.rect.height;
            var viewportHeight = _rewardList.viewport.rect.height;
            var maxScrollDistance = contentHeight - viewportHeight;
            var scrollDistance = Mathf.Max(0, maxScrollDistance - startPosition.y);

            float lineInterval = 0.35f;
            float groupInterval = 0.1f;
            int cellsRow = 5;

            if (scrollDistance > 0)
            {
                // スクロール開始前の待機時間
                await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

                // スクロール時間の計算
                var duration = 0f;

                // 合計アイテムの行数分の時間を加算
                var normalItemLines = (float)Math.Ceiling((decimal)iconViewModels.Count / cellsRow);
                duration += normalItemLines * lineInterval;

                // 周回数分のアイテムの行数分の時間を加算
                foreach (var groupIconViewModel in groupedIconViewModels)
                {
                    var boostItemLines = (float)Math.Ceiling((decimal)groupIconViewModel.Count / cellsRow);
                    duration += boostItemLines * lineInterval;
                }

                // グループ数分の間隔時間を加算
                duration += groupedIconViewModels.Count * groupInterval;

                // 合計表示の帯がある場合は追加の間隔時間を加算
                if (groupedIconViewModels.Count > 0)
                {
                    duration += groupInterval;
                }

                await DOTween.To(
                        () => content.localPosition.y,
                        value => content.localPosition = new Vector2(content.localPosition.x, value),
                        startPosition.y + scrollDistance,
                        duration)
                    .SetEase(Ease.Linear)
                    .OnComplete(() => isCellAnimationCompleted = true)
                    .ToUniTask(TweenCancelBehaviour.Complete, cancellationToken);
            }
            else
            {
                await UniTask.Delay(TimeSpan.FromSeconds(0.7f), cancellationToken: cancellationToken);
                isCellAnimationCompleted = true;
            }

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }

        public async UniTask PlaySpeedAttackResultAnimation(
            NewRecordFlag isNewRecord,
            StageClearTime clearTime,
            float duration,
            CancellationToken cancellationToken)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_053_009);

            var clearTimeMs = clearTime.ToMilliSeconds();
            await DOTween.To(
                    () => 0,
                    value => _speedAttackComponent.Setup(value),
                    clearTimeMs,
                    duration)
                .ToUniTask(TweenCancelBehaviour.Complete, cancellationToken);

            SoundEffectPlayer.Stop(SoundEffectId.SSE_053_009);

            if(isNewRecord)
            {
                _speedAttackComponent.ShowNewRecord();
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_010);
            }
        }

        public void SkipSlideInAnimation()
        {
            _animationController.SkipSlideIn();
        }

        public void SkipUserExpGainAnimation()
        {
            _animationController.SkipExpandRewardList();
        }

        public void SetInitialUserExp(UserExpGainViewModel userExpGainViewModel)
        {
            SetUserExpGain(
                userExpGainViewModel.Level,
                userExpGainViewModel.StartExp,
                userExpGainViewModel.NextLevelExp,
                userExpGainViewModel.IsLevelUp);
        }

        public void SetUserExpGain(UserExpGainViewModel userExpGainViewModel)
        {
            SetUserExpGain(
                userExpGainViewModel.Level,
                userExpGainViewModel.EndExp,
                userExpGainViewModel.NextLevelExp,
                userExpGainViewModel.IsLevelUp);
        }

        public void SetUserExpGain(UserLevel level, RelativeUserExp exp, RelativeUserExp nextLevelExp, bool isLevelUp)
        {
            _userLevelComponent.SetLevel(level, isLevelUp);

            var requiredExp = nextLevelExp - exp;
            _requiredExpText.SetText(requiredExp.ToString());

            _userExpGaugeImage.fillAmount = !nextLevelExp.IsZero() ? exp / nextLevelExp : 1f;

            // レベルアップ演出(もし再生中だったら出さない)
            if (isLevelUp && !_levelUpComponent.IsPlaying())
            {
                _levelUpComponent.Play();
            }
        }

        public void SkipAcquiredItems(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> groupedIconViewModels)
        {
            _rewardComponent.Hidden = false;
            _noAcquiredText.Hidden = 1 <= iconViewModels.Count;

            // セルアニメーションをスキップ
            _rewardComponent.SkipRewardCellAnimation();
            LayoutRebuilder.ForceRebuildLayoutImmediate(_rewardList.content);

            // スクロール位置を一番下に移動
            var content = _rewardList.content;
            var contentHeight = content.rect.height;
            var viewportHeight = _rewardList.viewport.rect.height;
            var scrollDistance = Mathf.Max(0, contentHeight - viewportHeight);

            if (scrollDistance > 0)
            {
                var startPosition = content.localPosition;
                content.localPosition = new Vector2(startPosition.x, startPosition.y + scrollDistance);
            }
        }

        public void HideSkipScreenButton()
        {
            _skipScreenButton.SetActive(false);
        }

        public void ShowTapLabel(string text)
        {
            _tapLabel.Show(text);
        }

        public void HideTapLabel()
        {
            _tapLabel.Hide();
        }

        public void SetUpEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            _eventCampaignBalloon.gameObject.SetActive(remainingTimeSpan.HasValue());
            _eventCampaignBalloon.SetRemainingTimeText(remainingTimeSpan);
        }

        public void ShouldShowRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            // アニメーション再生前に表示設定
            _retryObject.SetActive(isRetryAvailable);
        }

        public void SetInteractableRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            // アニメーション完了後にinteractableを設定
            _retryButton.interactable = isRetryAvailable;
        }

        async UniTask UpdateCanvases(CancellationToken cancellationToken)
        {
            await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
            Canvas.ForceUpdateCanvases();
            LayoutRebuilder.ForceRebuildLayoutImmediate(_rewardList.content);
        }
    }
}
