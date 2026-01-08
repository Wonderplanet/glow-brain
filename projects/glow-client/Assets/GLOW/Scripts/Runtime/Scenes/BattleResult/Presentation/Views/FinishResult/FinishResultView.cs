using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.Components.FinishResult;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-6_リザルト画面
    /// </summary>
    public class FinishResultView : UIView
    {
        [SerializeField] GameObject _resultRoot;
        [SerializeField] Button _skipScreenButton;
        [SerializeField] FinishResultAnimationController _animationController;
        [SerializeField] UIText _rewardMultiplierText;
        [SerializeField] UIText _currentScoreText;
        [SerializeField] UIText _highScoreText;
        [SerializeField] UIObject _rewardMultiplierRoot;
        [SerializeField] UIObject _newRecord;
        [SerializeField] PlayerResourceIconList _iconList;
        [SerializeField] FinishResultTapLabelComponent _tapLabel;
        [SerializeField] EventCampaignBalloon _eventCampaignBalloon;
        [SerializeField] UIObject _retryRoot;
        [SerializeField] Button _retryButton;

        protected override void Awake()
        {
            base.Awake();
            
            // 誤操作防止のため初期状態でinteractableをfalseにしておく
            _retryButton.interactable = false;
        }

        public async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            _resultRoot.SetActive(true);
            await _animationController.SlideIn(cancellationToken);
        }

        /// <summary> スコアを0~獲得スコアまで増やす </summary>
        public async UniTask IncreaseScoreAnimation(InGameScore currentScore, CancellationToken cancellationToken)
        {
            float duration = 1.0f;
            var resultScore = currentScore.ToLong();
            long currentAnimationScore = 0;
            if (!currentScore.IsZero())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_008);
            }
            await DOTween.To(
                    () => currentAnimationScore,
                    x => currentAnimationScore = x,
                    resultScore,
                    duration)
                .SetEase(Ease.Linear)
                .OnUpdate(() => _currentScoreText.SetText(AmountFormatter.FormatAmount(currentAnimationScore)))
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);
            _currentScoreText.SetText(AmountFormatter.FormatAmount(resultScore));
        }

        public async UniTask PlayScoreAnimation(CancellationToken cancellationToken)
        {
            await _animationController.ShowScore(cancellationToken);
        }

        public async UniTask PlayNewRecordAnimation(CancellationToken cancellationToken)
        {
            _newRecord.Hidden = false;
            await _animationController.NewRecord(cancellationToken);
        }

        /// <summary> 報酬アイテム順次表示 </summary>
        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            CancellationToken cancellationToken)
        {
            await _animationController.ExpandRewardList(cancellationToken);

            _iconList.Hidden = false;

            // 0の時はonCompleteが呼ばれず待ちが終わらないため処理を飛ばす
            if (iconViewModels.Count <= 0)
            {
                return;
            }

            var isCellAnimationCompleted = false;
            _iconList.SetupAndReload(iconViewModels, true, 1, onComplete:() => isCellAnimationCompleted = true);

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }

        public void SkipSlideInAnimation()
        {
            _animationController.SkipSlideIn();
        }

        public void SkipScoreAnimation()
        {
            _animationController.SkipScore();
        }

        public void SkipNewRecordAnimation()
        {
            _newRecord.Hidden = false;
            _animationController.SkipNewRecord();
        }

        public void SkipExpandRewardList()
        {
            _animationController.SkipExpandRewardList();
        }

        public void HideSkipScreenButton()
        {
            _skipScreenButton.gameObject.SetActive(false);
        }

        public void ShowTapLabel(string text)
        {
            _tapLabel.Show(text);
        }

        /// <summary> 報酬倍率表示設定 </summary>
        public void SetRewardMultiplierText(EventBonusPercentage totalBonusPercentage)
        {
            _rewardMultiplierRoot.Hidden = false;
            _rewardMultiplierText.SetText(totalBonusPercentage.GetMultiplierText());
        }

        /// <summary> スコア設定 </summary>
        public void SetScoreText(InGameScore currentScore)
        {
            _currentScoreText.SetText(AmountFormatter.FormatAmount(currentScore.ToLong()));
        }

        /// <summary> ハイスコア表示設定 </summary>
        public void SetHighScoreText(InGameScore highScore, NewRecordFlag newRecordFlag)
        {
            _highScoreText.SetText(AmountFormatter.FormatAmount(highScore.ToLong()));
        }

        /// <summary> 報酬リスト即時表示 </summary>
        public void SetAcquiredItems(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels)
        {
            _iconList.Hidden = false;
            _iconList.PlayerResourceIconAnimation?.SkipAnimation();

            if (iconViewModels.Count <= 0 || !_iconList.IsEmptyIconViewModels)
            {
                return;
            }

            // 表示する報酬があるはずなのにリストにデータが設定されていないのであれば
            // データ設定(SetupAndReload)前にキャンセルされているので改めてデータ設定を行う
            _iconList.SetupAndReload(iconViewModels, false);
        }

        public void SetUpEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            _eventCampaignBalloon.gameObject.SetActive(remainingTimeSpan.HasValue());
            _eventCampaignBalloon.SetRemainingTimeText(remainingTimeSpan);
        }

        public void SetOnPlayerResourceIconTapped(Action<PlayerResourceIconViewModel> onPlayerResourceIconTapped)
        {
            _iconList.OnPlayerResourceIconTapped = onPlayerResourceIconTapped;
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
