using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCostTextComponent : UIObject
    {
        [SerializeField] ShopDiamondCostTextComponent _diamondCostComponent;

        [SerializeField] ShopCoinCostTextComponent _coinCostComponent;

        [SerializeField] ShopCostFreeTextComponent _freeCostComponent;
        
        [SerializeField] ShopStorePriceTextComponent _storePriceTextComponent;

        public void Setup(
            DisplayCostType costType,
            CostAmount costAmount,
            RawProductPriceText price,
            IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            if (costType == DisplayCostType.Cash)
            {
                _diamondCostComponent.Hidden = true;
                _coinCostComponent.Hidden = true;
                _freeCostComponent.Hidden = true;
                _storePriceTextComponent.Hidden = false;
                _storePriceTextComponent.Setup(price);
                return;
            }
            
            _storePriceTextComponent.Hidden = true;
            _diamondCostComponent.Setup(costType, costAmount, isFirstTimeFreeDisplay);
            _coinCostComponent.Setup(costType, costAmount, isFirstTimeFreeDisplay);
            _freeCostComponent.Setup(isFirstTimeFreeDisplay);
        }
    }
}
