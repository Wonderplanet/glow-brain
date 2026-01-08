using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class ItemBoxIconList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        IReadOnlyList<ItemIconViewModel> _otherViewModels = new List<ItemIconViewModel>();

        public Action<MasterDataId> OnItemIconTapped { get; set; }

        public void InitializeItemList()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetupAndReload(IReadOnlyList<ItemIconViewModel> otherViewModels)
        {
            _otherViewModels = otherViewModels;
            _collectionView.ReloadData();
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _otherViewModels?.Count ?? 0;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ItemBoxIconListCell>();
            var viewModel = _otherViewModels[indexPath.Row];

            cell.Setup(viewModel);
            cell.Show();
            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _otherViewModels[indexPath.Row];
            OnItemIconTapped?.Invoke(viewModel.ItemId);
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            // no use.
        }
    }
}
