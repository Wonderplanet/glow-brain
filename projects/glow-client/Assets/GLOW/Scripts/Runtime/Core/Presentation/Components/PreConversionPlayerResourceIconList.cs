using System;
using System.Collections.Generic;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Extensions;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class PreConversionPlayerResourceIconList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] int _shouldScrollItemCount = 15;

        IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> _iconViewModels = new List<PlayerResourceIconWithPreConversionViewModel>();

        IPlayerResourceIconAnimation _playerResourceIconAnimation;
        bool _isAnimation = true;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped { get; set; }

        public IPlayerResourceIconAnimation PlayerResourceIconAnimation
        {
            get => _playerResourceIconAnimation;
        }

        public RectOffset Padding => _collectionView.Padding;

        public bool IsEmptyIconViewModels => _iconViewModels.IsEmpty();

        public void SetupAndReload(
            IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> iconViewModels,
            bool isCellAnimation = true,
            int startScrollRow = 1,
            Action onComplete = null)
        {
            _collectionView.ScrollRect.enabled = iconViewModels.Count > _shouldScrollItemCount;
            _playerResourceIconAnimation?.Dispose();
            _playerResourceIconAnimation = null;

            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
            _isAnimation = isCellAnimation;

            if (isCellAnimation)
            {
                _playerResourceIconAnimation = _collectionView.CreateCommonReceiveAnimation();
                _playerResourceIconAnimation.ScrollAnimation(iconViewModels.Count, startScrollRow, onComplete);
            }

            _iconViewModels = iconViewModels;
            _collectionView.ReloadData();

            if (isCellAnimation)
            {
                // アニメーションの表示チラつき防止に1フレーム待つ
                _playerResourceIconAnimation.SkipOneFrame();
            }

            if (iconViewModels.IsEmpty())
            {
                _playerResourceIconAnimation?.SkipAnimation();
            }
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _iconViewModels.Count;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PreConversionPlayerResourceIconListCell>();
            var viewModel = _iconViewModels[indexPath.Row];

            cell.Setup(viewModel);

            if (_isAnimation)
            {
                _playerResourceIconAnimation?.CellAnimation(cell, indexPath.Row, _iconViewModels.Count);
                if (indexPath.Row >= _iconViewModels.Count - 1)
                    _isAnimation = false;
            }

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _iconViewModels[indexPath.Row];
            if (!viewModel.ConvertedPlayerResourceIcon.IsEmpty())
            {
                OnPlayerResourceIconTapped?.Invoke(viewModel.ConvertedPlayerResourceIcon);
                return;
            }
            OnPlayerResourceIconTapped?.Invoke(viewModel.PlayerResourceIcon);
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
