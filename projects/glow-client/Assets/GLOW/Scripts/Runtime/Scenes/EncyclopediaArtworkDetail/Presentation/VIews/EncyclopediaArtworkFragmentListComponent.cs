using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkFragmentListComponent : UIObject
    ,IUICollectionViewDataSource
    ,IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<EncyclopediaArtworkFragmentListCellViewModel> _cellViewModels;
        Action<EncyclopediaArtworkFragmentListCellViewModel> _onSelectFragment;

        protected override void Awake()
        {
            base.Awake();
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void Setup(
            IReadOnlyList<EncyclopediaArtworkFragmentListCellViewModel> cellViewModels,
            Action<EncyclopediaArtworkFragmentListCellViewModel> onSelectFragment)
        {
            _cellViewModels = cellViewModels;
            _onSelectFragment = onSelectFragment;
            _collectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EncyclopediaArtworkFragmentListCell>();
            var viewModel = _cellViewModels[indexPath.Row];
            cell.Setup(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath) { }
        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            var viewModel = _cellViewModels[indexPath.Row];
            _onSelectFragment?.Invoke(viewModel);
        }
    }
}
