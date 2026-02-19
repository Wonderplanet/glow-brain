using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattleRaidTotalScoreRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<AdventBattleRaidTotalScoreRewardCellViewModel> _raidTotalScoreRewardCellViewModels;
        Action<PlayerResourceIconViewModel> _rewardIconAction;

        protected override void Awake()
        {
            base.Awake();

            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }
        
        public void SetupRaidTotalScoreRewardList(
            IReadOnlyList<AdventBattleRaidTotalScoreRewardCellViewModel> raidTotalScoreRewardCellViewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _raidTotalScoreRewardCellViewModels = raidTotalScoreRewardCellViewModels;
            _rewardIconAction = rewardIconAction;

            SetupScroll();
            
            _collectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(
            UICollectionView collectionView, 
            int section)
        {
            return _raidTotalScoreRewardCellViewModels.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdventBattleRaidTotalScoreRewardListCell>();
            var viewModel = _raidTotalScoreRewardCellViewModels[indexPath.Row];
            cell.SetupRaidScoreReward(viewModel, _rewardIconAction);

            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(
            UICollectionView collectionView, 
            UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath, object identifier)
        {
        }
        
        void SetupScroll()
        {
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
        }
    }
}