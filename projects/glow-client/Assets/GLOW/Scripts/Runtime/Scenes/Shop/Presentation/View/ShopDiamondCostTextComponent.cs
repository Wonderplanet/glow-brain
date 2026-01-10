using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopDiamondCostTextComponent : UIObject
    {
        [SerializeField] UIText _costAmountText;

        public void Setup(DisplayCostType costType, CostAmount costAmount, IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            if (!IsVisibleFromType(costType, isFirstTimeFreeDisplay))
            {
                Hidden = true;
                return;
            }

            Hidden = false;
            _costAmountText.SetText("×{0}", costAmount.ToString());
            _costAmountText.Hidden = false;
        }

        bool IsVisibleFromType(DisplayCostType costType, IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            return (costType is DisplayCostType.Diamond) && !isFirstTimeFreeDisplay.IsEnable();
        }
    }
}
