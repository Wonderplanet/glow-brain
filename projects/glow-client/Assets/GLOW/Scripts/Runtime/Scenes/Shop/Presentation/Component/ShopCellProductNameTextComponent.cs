using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellProductNameTextComponent : UIBehaviour
    {
        [SerializeField] UIText _productNameText;
        
        public void Setup(
            ProductName productName,
            ResourceType resourceType,
            ProductResourceAmount amount)
        {
            if (resourceType == ResourceType.Item)
            {
                _productNameText.SetText(ProductName.WithProductResourceAmount(productName, amount).Value);
            }
            else
            {
                _productNameText.SetText(ProductName.FromResourceTypeWithProductResourceAmount(resourceType, amount).Value);
            }
        }
    }
}