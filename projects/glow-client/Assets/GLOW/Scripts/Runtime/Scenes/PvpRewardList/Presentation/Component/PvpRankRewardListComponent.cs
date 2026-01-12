using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpRankRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        
        Action<PlayerResourceIconViewModel> _rewardIconAction;
        
        IReadOnlyList<PvpRankRewardCellViewModel> _pvpPointRankRewardCellViewModels =
            new List<PvpRankRewardCellViewModel>();
        
        public void Initialize()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetUpRankRewardList(
            IReadOnlyList<PvpRankRewardCellViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpPointRankRewardCellViewModels = viewModels;
            _rewardIconAction = rewardIconAction;
            
            InitializeScrollPos();
            
            _collectionView.ReloadData();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _pvpPointRankRewardCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PvpRankRewardListCell>();
            var viewModel = _pvpPointRankRewardCellViewModels[indexPath.Row];
            cell.SetUpTotalPointRewardComponent(
                viewModel.Rewards,
                _rewardIconAction);
            cell.SetUpRequiredTotalScoreText(viewModel.RequiredPoint);
            cell.SetUpRankIcon(viewModel.RankType, viewModel.RankLevel);
            cell.SetUpRankName(viewModel.RankType, viewModel.RankLevel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
        
        void InitializeScrollPos()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
    }
}