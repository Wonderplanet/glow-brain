using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class SelectionFragmentLineupListCell : UICollectionViewCell
    {
        [SerializeField] SimpleItemIconComponent _itemIcon;
        [SerializeField] UIText _itemNameText;
        [SerializeField] UIToggleableComponent _toggle;

        public FragmentLineupListDataSourceIndex DataSourceIndex { get; private set; }

        public bool IsSelected {
            get => _toggle.IsToggleOn;
            set => _toggle.IsToggleOn = value;
        }

        public void Setup(SelectableLineupFragmentViewModel viewModel, FragmentLineupListDataSourceIndex dataSourceIndex)
        {
            DataSourceIndex = dataSourceIndex;

            _itemIcon.Setup(viewModel.ItemIconAssetPath, viewModel.Rarity);
            _itemNameText.SetText(viewModel.Name.ToString());
            _toggle.IsToggleOn = viewModel.IsSelected;
        }
    }
}
