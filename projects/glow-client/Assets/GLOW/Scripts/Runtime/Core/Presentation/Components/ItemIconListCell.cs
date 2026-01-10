using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class ItemIconListCell : UICollectionViewCell
    {
        [SerializeField] ItemIconComponent _itemIconComponent;

        public void Setup(ItemIconViewModel viewModel)
        {
            _itemIconComponent.Setup(viewModel.ItemIconAssetPath, viewModel.Rarity, viewModel.Amount);
        }
    }
}
