using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattlePersonalRewardListComponent : UIObject
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<IAdventBattlePersonalCellViewModel> _personalScoreRewardCellViewModels;
        Action<PlayerResourceIconViewModel> _rewardIconAction;
        
        protected override void Awake()
        {
            base.Awake();
            
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }
        
        public void SetupPersonalRewardList(
            IReadOnlyList<IAdventBattlePersonalCellViewModel> personalScoreRewardCellViewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _personalScoreRewardCellViewModels = personalScoreRewardCellViewModels;
            _rewardIconAction = rewardIconAction;

            InitializeScrollPos();
            
            _collectionView.ReloadData();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _personalScoreRewardCellViewModels.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdventBattlePersonalRewardListCell>();
            var viewModel = _personalScoreRewardCellViewModels[indexPath.Row];
            cell.SetupPersonalScoreReward(
                viewModel, 
                _rewardIconAction);
            
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