using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class ShopDiscountRateComponent : MonoBehaviour
    {
        [SerializeField] UIText _text;

        public void SetDiscountRate(DiscountRate discountRate)
        {
            _text.SetText("{0}", discountRate.Value);
            this.gameObject.SetActive(!discountRate.IsEmpty());
        }
    }
}
