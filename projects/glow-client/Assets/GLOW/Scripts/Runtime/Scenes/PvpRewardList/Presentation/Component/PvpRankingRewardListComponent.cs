using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpRankingRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        
        Action<PlayerResourceIconViewModel> _rewardIconAction;

        IReadOnlyList<IPvpRankingRewardCellViewModel> _pvpRankingRewardCellViewModels =
            new List<IPvpRankingRewardCellViewModel>();

        public void Initialize()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }
        
        public void SetUpRankingRewardList(
            IReadOnlyList<IPvpRankingRewardCellViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpRankingRewardCellViewModels = viewModels;
            _rewardIconAction = rewardIconAction;

            InitializeScrollPos();
            
            _collectionView.ReloadData();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(
            UICollectionView collectionView, 
            int section)
        {
            return _pvpRankingRewardCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView, 
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PvpRankingRewardListCell>();
            var viewModel = _pvpRankingRewardCellViewModels[indexPath.Row];
            cell.SetUpRankingRewardComponent(
                viewModel.Rewards, 
                _rewardIconAction);
            cell.SetUpRankingCell(viewModel.RankingRankUpper);
            cell.SetUpRankingRankText(viewModel.RankingRankUpper, viewModel.RankingText);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(
            UICollectionView collectionView, 
            UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView, 
            UIIndexPath indexPath, 
            object identifier)
        {
        }
        
        void InitializeScrollPos()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
    }
}