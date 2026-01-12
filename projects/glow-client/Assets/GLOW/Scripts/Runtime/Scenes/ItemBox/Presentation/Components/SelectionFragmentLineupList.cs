using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class SelectionFragmentLineupList : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] Transform _contentTransform;
        [SerializeField] ChildScaler _childScaler;

        IReadOnlyList<SelectableLineupFragmentViewModel> _fragmentViewModels = new List<SelectableLineupFragmentViewModel>();

        public SelectableLineupFragmentViewModel SelectedFragment => _fragmentViewModels
            .FirstOrDefault(fragment => fragment.IsSelected, SelectableLineupFragmentViewModel.Empty);

        public void Setup(IReadOnlyList<SelectableLineupFragmentViewModel> fragmentViewModels)
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
            var cell = collectionView.DequeueReusableCell<SelectionFragmentLineupListCell>();
            var viewModel = _fragmentViewModels[indexPath.Row];

            var dataSourceIndex = new FragmentLineupListDataSourceIndex(indexPath.Row);
            cell.Setup(viewModel, dataSourceIndex);

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            _fragmentViewModels = _fragmentViewModels
                .Select((fragment, i) => fragment with { IsSelected = i == indexPath.Row })
                .ToList();

            foreach (Transform cell in _contentTransform)
            {
                var lineupListCell = cell.GetComponent<SelectionFragmentLineupListCell>();
                if (lineupListCell == null) continue;

                var viewModel = GetFragmentViewModel(lineupListCell.DataSourceIndex);
                lineupListCell.IsSelected = viewModel.IsSelected;
            }
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        SelectableLineupFragmentViewModel GetFragmentViewModel(FragmentLineupListDataSourceIndex dataSourceIndex)
        {
            if (dataSourceIndex.IsEmpty()) return SelectableLineupFragmentViewModel.Empty;

            return _fragmentViewModels[dataSourceIndex.Value];
        }
    }
}
