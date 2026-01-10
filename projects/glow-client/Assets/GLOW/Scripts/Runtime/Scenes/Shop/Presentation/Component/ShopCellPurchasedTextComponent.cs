using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellPurchasedTextComponent : UIBehaviour
    {
        [SerializeField] UIText _purchasedText;
        
        public void Setup(DisplayCostType costType)
        {
            if (costType == DisplayCostType.Ad)
            {
                _purchasedText.SetText("獲得済み");
            }
            else if (costType == DisplayCostType.Coin)
            {
                _purchasedText.SetText("交換済み");
            }
            else
            {
                _purchasedText.SetText("購入済み");
            }
        }
    }
}