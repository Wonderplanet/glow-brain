using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Component
{
    public class MessageBoxDetailRewardComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        const int _maxVisibleRewardCount = 5;

        [SerializeField] UICollectionView _rewardCollection;

        [SerializeField] GameObject _rightArrow;
        [SerializeField] GameObject _leftArrow;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        IReadOnlyList<PlayerResourceIconViewModel> _rewardModels = new List<PlayerResourceIconViewModel>();

        protected override void Awake()
        {
            base.Awake();

            _rewardCollection.DataSource = this;
            _rewardCollection.Delegate = this;

            _rightArrow.gameObject.SetActive(false);
            _leftArrow.gameObject.SetActive(false);
        }

        public void Setup(IReadOnlyList<PlayerResourceIconViewModel> viewModels)
        {
            _rewardModels = viewModels;

            if (_rewardModels.Count <= _maxVisibleRewardCount) return;

            _rewardCollection.ScrollRect.onValueChanged.AddListener((normalizedPos =>
            {
                UpdateArrowButtonVisible(normalizedPos.x);
            }));
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _rewardModels.Count;
        }

        void UpdateArrowButtonVisible(float normalizedPosX)
        {
            var threshold = 0.001f;
            _rightArrow.gameObject.SetActive(normalizedPosX + threshold < 1);
            _leftArrow.gameObject.SetActive(normalizedPosX > threshold);
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PlayerResourceIconListCell>();
            var viewModel = _rewardModels[indexPath.Row];
            cell.Setup(viewModel);
            cell.PlayAppearanceAnimation(1.0f);

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _rewardModels[indexPath.Row];
            OnPlayerResourceIconTapped?.Invoke(viewModel);
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier){}
    }
}
