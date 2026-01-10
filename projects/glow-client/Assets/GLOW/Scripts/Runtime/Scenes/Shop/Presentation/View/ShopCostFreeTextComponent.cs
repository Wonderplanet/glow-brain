using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCostFreeTextComponent : UIObject
    {
        [SerializeField] UIText _freeText;

        public void Setup(IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            if (!IsVisibleFromType(isFirstTimeFreeDisplay))
            {
                Hidden = true;
                return;
            }

            Hidden = false;
            _freeText.Hidden = false;
        }

        bool IsVisibleFromType(IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            return isFirstTimeFreeDisplay.IsEnable();
        }
    }
}
