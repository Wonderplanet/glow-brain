using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattle.Presentation.ValueObject;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleHighScoreRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] UIImage _lastRewardBackgroundImage;
        [SerializeField] Button _rightArrowButton;
        [SerializeField] Button _leftArrowButton;
        [SerializeField] AdventBattleHighScorePickUpRewardComponent _adventBattleHighScorePickUpRewardComponent;
        [SerializeField] MissionProgressGaugeComponent _missionProgressGaugeComponent;
        
        IReadOnlyList<AdventBattleHighScoreRewardViewModel> _highScoreRewards;
        Action<PlayerResourceIconViewModel> _rewardAction;

        const int DefaultCellDisplayCount = 3;
        
        protected override void Awake()
        {
            base.Awake();
            
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }
        
        public void Setup(
            IReadOnlyList<AdventBattleHighScoreRewardViewModel> highScoreRewards,
            AdventBattleHighScoreGaugeViewModel gaugeViewModel,
            Action<PlayerResourceIconViewModel> rewardAction)
        {
            _highScoreRewards = highScoreRewards;
            _rewardAction = rewardAction;
            _lastRewardBackgroundImage.Hidden = false;
            _missionProgressGaugeComponent.SetProgressGaugeRate(gaugeViewModel.CurrentGaugeRate.Value);
            _collectionView.ReloadData();
            
            var pickUpReward = highScoreRewards.FirstOrDefault(
                reward => reward.PickupFlag,
                AdventBattleHighScoreRewardViewModel.Empty);
            _adventBattleHighScorePickUpRewardComponent.Setup(
                pickUpReward,
                rewardAction);
            
            _rightArrowButton.onClick.AddListener(AddPositionRight);
            _leftArrowButton.onClick.AddListener(AddPositionLeft);
            
            // スクロール時にスクロールの位置で左右の矢印を表示を切り替える
            _collectionView.ScrollRect.onValueChanged.AddListener((normalizedPos) =>
            {
                UpdateArrowButtonVisible(normalizedPos.x);
            });
            
            // 初期表示
            UpdateArrowButtonVisible(_collectionView.ScrollRect.normalizedPosition.x);
            
            // スクロール上の初期位置
            var firstDisplayReward = highScoreRewards
                .Where(reward => !reward.ObtainedFlag)
                .OrderBy(reward => reward.AdventBattleHighScore)
                .FirstOrDefault(pickUpReward);
            var firstDisplayIndex = highScoreRewards.IndexOf(firstDisplayReward);
            _collectionView.ScrollToRowAt(
                new UIIndexPath(0, firstDisplayIndex), 
                UICollectionView.ScrollPosition.None, 
                false);
        }
        
        public async UniTask PlayHighScoreGaugeScrollAnimation(
            CancellationToken cancellationToken, 
            AdventBattleHighScoreGaugeRate rate)
        {
            await _missionProgressGaugeComponent.PlayProgressGaugeAnimation(
                cancellationToken, 
                rate.Value,
                0.15f);
        }
        
        public void PlayScrollToNextRewardAnimation()
        {
            EasingScrollCellToCenter(ScrollAnimationDirection.Right, false, 0.3f);
        }
        
        public async UniTask PlayObtainRewardAnimation(CancellationToken cancellationToken, HighScoreRewardCellIndex obtainedRewardIndex)
        {
            var rewardCell = _collectionView.CellForRow(new UIIndexPath(0, obtainedRewardIndex.Value)) as AdventBattleHighScoreRewardListCell;
            if (rewardCell == null) return;
            
            await rewardCell.PlayObtainRewardAnimation(cancellationToken);
        }
        
        public void UpdatedHighScoreRewardsAfterObtained(
            IReadOnlyList<AdventBattleHighScoreRewardViewModel> highScoreRewards,
            Action<PlayerResourceIconViewModel> rewardAction)
        {
            _highScoreRewards = highScoreRewards;
            
            var pickUpReward = highScoreRewards.Last();
            _adventBattleHighScorePickUpRewardComponent.Setup(
                pickUpReward,
                rewardAction);
        }
        
        public void PlayPickUpRewardEffect()
        {
            _adventBattleHighScorePickUpRewardComponent.PlayPickUpRewardEffect();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _highScoreRewards?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdventBattleHighScoreRewardListCell>();
            var viewModel = _highScoreRewards[indexPath.Row];
            cell.SetRewardIcon(viewModel.RewardViewModel, viewModel.ObtainedFlag);
            cell.SetRewardIconTappedAction(viewModel.RewardViewModel, _rewardAction);
            cell.SetHighScoreComponent(viewModel.AdventBattleHighScore, viewModel.PickupFlag);
            cell.SetupObtainedRewardIcon(viewModel.ObtainedFlag);
            cell.PlayPickUpRewardAnimation(viewModel.ObtainedFlag, viewModel.PickupFlag);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath) { }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier) { }

        void AddPositionRight()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            EasingScrollCellToCenter(ScrollAnimationDirection.Right, true, 0.3f);
        }
        
        void AddPositionLeft()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            EasingScrollCellToCenter(ScrollAnimationDirection.Left, true, 0.3f);
        }

        void EasingScrollCellToCenter(ScrollAnimationDirection direction, bool withEasing, float duration)
        {
            var scrollRect = _collectionView.ScrollRect;
            var viewportWidth = scrollRect.viewport.rect.width;
            var enableScrollAreaWidth = scrollRect.content.rect.width - viewportWidth;
            var cellWidth = viewportWidth / DefaultCellDisplayCount;
            var slideInterval = cellWidth / enableScrollAreaWidth;
            var currentPos = scrollRect.normalizedPosition.x * enableScrollAreaWidth;
            
            int nextIndex;
            if (direction == ScrollAnimationDirection.Right)
            {
                // 現状のスクロール位置から一番真ん中に近い次のインデックスを取得
                // Math.Roundは誤差防止のため
                nextIndex = Mathf.FloorToInt((float)Math.Round(currentPos, 0) / cellWidth) + 1;
            }
            else if(direction == ScrollAnimationDirection.Left)
            {
                // 現状のスクロール位置から一番真ん中に近い前のインデックスを取得
                // Math.Roundは誤差防止のため
                nextIndex = Mathf.CeilToInt((float)Math.Round(currentPos, 0) / cellWidth) - 1;
            }
            else
            {
                return;
            }
            
            var normalizedPos = Mathf.Clamp01(nextIndex * slideInterval);

            if (withEasing)
            {
                scrollRect.DOHorizontalNormalizedPos(normalizedPos, duration).SetEase(Ease.InOutExpo);
            }
            else
            {
                scrollRect.DOHorizontalNormalizedPos(normalizedPos, duration);
            }
        }
        
        void UpdateArrowButtonVisible(float normalizedPosX)
        {
            var threshold = 0.001f;
            _rightArrowButton.gameObject.SetActive(normalizedPosX + threshold < 1);
            _leftArrowButton.gameObject.SetActive(normalizedPosX > threshold);
        }
        
    }
}