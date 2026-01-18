using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BoxGacha.Presentation.Component
{
    public class BoxGachaRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<BoxGachaRewardListCellViewModel> _cellViewModels = new List<BoxGachaRewardListCellViewModel>();

        Action<PlayerResourceIconViewModel> _onPrizeIconTapped;
        
        public void Initialize()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetUpRewardList(
            IReadOnlyList<BoxGachaRewardListCellViewModel> cellViewModels,
            Action<PlayerResourceIconViewModel> onPrizeIconTapped)
        {
            _cellViewModels = cellViewModels;
            _onPrizeIconTapped = onPrizeIconTapped;
            _collectionView.ReloadData();
        }
        
        public void ResetScrollPosition()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellViewModels.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView, 
            UIIndexPath indexPath)
        {
            var cell = _collectionView.DequeueReusableCell<BoxGachaRewardListCellComponent>();
            var viewModel = _cellViewModels[indexPath.Row];
            if (viewModel == null) return cell;
            
            cell.SetUpCell(viewModel, _onPrizeIconTapped);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView, 
            UIIndexPath indexPath, 
            object identifier)
        {
            
        }
    }
}