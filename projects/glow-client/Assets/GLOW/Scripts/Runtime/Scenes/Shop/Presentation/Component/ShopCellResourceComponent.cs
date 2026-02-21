using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellResourceComponent : UIBehaviour
    {
        [SerializeField] ShopProductResourceSpriteComponent _coinSpriteComponent;
        [SerializeField] ShopProductResourceSpriteComponent _diamondSpriteComponent;
        [SerializeField] ItemIconComponent _itemIconComponent;

        public void Setup(ShopProductCellViewModel viewModel)
        {
            if (viewModel.IsItemIconInvisible())
            {
                _itemIconComponent.Hidden = true;
                if (viewModel.ResourceType == ResourceType.Coin || viewModel.ResourceType == ResourceType.IdleCoin)
                {
                    _coinSpriteComponent.Hidden = false;
                    _coinSpriteComponent.SetUp(
                        viewModel.DisplayAdvertisementResourceAmount(),
                        viewModel.ShopProductAssetPath,
                        true);
                    _diamondSpriteComponent.Hidden = true;
                }
                else if (viewModel.ResourceType == ResourceType.FreeDiamond || viewModel.ResourceType == ResourceType.PaidDiamond)
                {
                    _diamondSpriteComponent.Hidden = false;
                    _diamondSpriteComponent.SetUp(
                        viewModel.DisplayAdvertisementResourceAmount(),
                        viewModel.ShopProductAssetPath,
                        false);
                    _coinSpriteComponent.Hidden = true;
                }
            }
            else
            {
                _coinSpriteComponent.Hidden = true;
                _diamondSpriteComponent.Hidden = true;
                _itemIconComponent.Hidden = false;
                var itemIconViewModel = viewModel.ItemIconViewModel;
                _itemIconComponent.Setup(itemIconViewModel.ItemIconAssetPath,
                    itemIconViewModel.Rarity,
                    itemIconViewModel.Amount);
            }
        }
    }
}
