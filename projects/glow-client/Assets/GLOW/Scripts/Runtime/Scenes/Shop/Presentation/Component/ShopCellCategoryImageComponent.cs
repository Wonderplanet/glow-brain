using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellCategoryImageComponent : UIBehaviour
    {
        [SerializeField] UIImage _baseImage;
        [SerializeField] Sprite _adBaseSprite;
        [SerializeField] Sprite _normalBaseSprite;
        
        public void Setup(DisplayCostType costType)
        {
            _baseImage.Sprite = GetBaseSprite(costType);
        }
        
        Sprite GetBaseSprite(DisplayCostType costType)
        {
            if (costType == DisplayCostType.Ad)
            {
                return _adBaseSprite;
            }
            
            return _normalBaseSprite;
        }
    }
}