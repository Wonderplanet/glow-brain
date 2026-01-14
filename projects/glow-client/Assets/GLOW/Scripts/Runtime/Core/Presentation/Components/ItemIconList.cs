using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class ItemIconList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<ItemIconViewModel> _iconViewModels = new List<ItemIconViewModel>();

        public Action<MasterDataId> OnItemIconTapped { get; set; }

        public void SetupAndReload(IReadOnlyList<ItemIconViewModel> iconViewModels)
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;

            _iconViewModels = iconViewModels;
            _collectionView.ReloadData();
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _iconViewModels.Count;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ItemIconListCell>();
            var viewModel = _iconViewModels[indexPath.Row];

            cell.Setup(viewModel);

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _iconViewModels[indexPath.Row];
            OnItemIconTapped?.Invoke(viewModel.ItemId);
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
