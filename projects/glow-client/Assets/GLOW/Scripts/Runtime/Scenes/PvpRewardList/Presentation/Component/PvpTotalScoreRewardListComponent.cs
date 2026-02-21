using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpTotalScoreRewardListComponent :
        UIObject,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        
        Action<PlayerResourceIconViewModel> _onRewardIconAction;
        
        IReadOnlyList<PvpTotalScoreRewardCellViewModel> _pvpTotalPointRewardCellViewModels =
            new List<PvpTotalScoreRewardCellViewModel>();
        
        public void Initialize()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetUpTotalPointRewardList(
            IReadOnlyList<PvpTotalScoreRewardCellViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpTotalPointRewardCellViewModels = viewModels;
            _onRewardIconAction = rewardIconAction;
            
            SetUpDefaultScrollPos();
            
            _collectionView.ReloadData();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _pvpTotalPointRewardCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PvpTotalScoreRewardListCell>();
            var viewModel = _pvpTotalPointRewardCellViewModels[indexPath.Row];
            cell.SetUpTotalPointRewardComponent(
                viewModel.Rewards,
                _onRewardIconAction);
            cell.SetUpRequiredTotalScoreText(viewModel.RequiredPoint);
            cell.SetUpReceivedObject(viewModel.IsReceived);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
        
        void SetUpDefaultScrollPos()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
    }
}