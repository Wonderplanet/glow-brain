using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Shop.Presentation.View;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellPurchasableCountPlateComponent : UIObject
    {
        [SerializeField] ShopPurchasableCountTextComponent _purchasableCountTextComponent;

        public void Setup(
            PurchasableCount count,
            DisplayCostType costType,
            IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            _purchasableCountTextComponent.Setup(count, costType, isFirstTimeFreeDisplay);
            Hidden = count.IsZero() || count.IsInfinity();
        }
    }
}
