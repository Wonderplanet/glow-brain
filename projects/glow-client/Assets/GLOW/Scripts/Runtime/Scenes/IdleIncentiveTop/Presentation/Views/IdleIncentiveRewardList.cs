using GLOW.Core.Presentation.Components;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public class IdleIncentiveRewardList : MonoBehaviour, IUICollectionViewDelegate, IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        IdleIncentiveRewardListViewModel _viewModel;

        public void Setup(IdleIncentiveRewardListViewModel viewModel)
        {
            _collectionView.Delegate = this;
            _collectionView.DataSource = this;
            
            _viewModel = viewModel;
            _collectionView.ReloadData();
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.Rewards.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<IdleIncentiveRewardListCell>();
            var viewModel = _viewModel.Rewards[indexPath.Row];
            cell.Setup(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier) { }
    }
}
