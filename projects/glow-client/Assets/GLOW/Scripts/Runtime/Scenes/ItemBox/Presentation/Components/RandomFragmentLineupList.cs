using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class RandomFragmentLineupList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<LineupFragmentViewModel> _fragmentViewModels = new List<LineupFragmentViewModel>();

        public void Setup(IReadOnlyList<LineupFragmentViewModel> fragmentViewModels)
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;

            _fragmentViewModels = fragmentViewModels;
            _collectionView.ReloadData();
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _fragmentViewModels.Count;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<RandomFragmentLineupListCell>();
            var viewModel = _fragmentViewModels[indexPath.Row];

            cell.Setup(viewModel);

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
