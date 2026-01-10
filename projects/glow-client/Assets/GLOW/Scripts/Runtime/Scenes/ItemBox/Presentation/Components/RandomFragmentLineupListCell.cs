using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class RandomFragmentLineupListCell : UICollectionViewCell
    {
        [SerializeField] SimpleItemIconComponent _itemIcon;
        [SerializeField] UIText _itemNameText;

        public void Setup(LineupFragmentViewModel viewModel)
        {
            _itemIcon.Setup(viewModel.ItemIconAssetPath, viewModel.Rarity);
            _itemNameText.SetText(viewModel.Name.ToString());
        }
    }
}
