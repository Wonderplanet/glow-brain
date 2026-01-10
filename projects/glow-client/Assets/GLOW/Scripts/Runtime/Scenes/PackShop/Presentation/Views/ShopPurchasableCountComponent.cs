using GLOW.Core.Domain.ValueObjects.Shop;
using TMPro;
using UnityEngine;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class ShopPurchasableCountComponent : MonoBehaviour
    {
        [SerializeField] TextMeshProUGUI _text;

        public void SetPurchasableCount(PurchasableCount count)
        {
            _text.text = $"あと{count.Value}回購入可能";
            this.gameObject.SetActive(!count.IsEmpty());
        }
    }
}
