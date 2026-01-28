using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UserEmblem.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UserEmblem.Presentation.Views.Component
{
    public class UserEmblemCellList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        IReadOnlyList<HeaderUserEmblemCellViewModel> _viewModels = new List<HeaderUserEmblemCellViewModel>();
        MasterDataId _selectedId;

        public Action<MasterDataId> OnIconTapped { get; set; }

        public void Setup()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void Reload(IReadOnlyList<HeaderUserEmblemCellViewModel> viewModels, MasterDataId selectedId)
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
            var cell = collectionView.DequeueReusableCell<UserEmblemCell>(item=> item.MstEmblemId == viewModel.Id);

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
