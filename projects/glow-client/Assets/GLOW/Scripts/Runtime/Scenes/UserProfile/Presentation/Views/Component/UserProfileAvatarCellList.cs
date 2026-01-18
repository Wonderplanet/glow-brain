using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UserProfile.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UserProfile.Presentation.Views.Component
{
    public class UserProfileAvatarCellList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;
        
        IReadOnlyList<UserProfileAvatarCellViewModel> _viewModels = new List<UserProfileAvatarCellViewModel>();
        MasterDataId _selectedId;

        public Action<MasterDataId> OnIconTapped { get; set; }

        public void InitializeCollectionView()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetupAndReload(IReadOnlyList<UserProfileAvatarCellViewModel> viewModels, MasterDataId selectedId)
        {
            _viewModels = viewModels;
            _selectedId = selectedId;
            _collectionView.ReloadData();
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModels.Count;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModels[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<UserProfileAvatarCell>(item=> item.MstAvaterId == viewModel.Id);

            cell.Setup(viewModel);
            cell.IsSelected = viewModel.Id == _selectedId;

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModels[indexPath.Row];
            OnIconTapped?.Invoke(viewModel.Id);
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
