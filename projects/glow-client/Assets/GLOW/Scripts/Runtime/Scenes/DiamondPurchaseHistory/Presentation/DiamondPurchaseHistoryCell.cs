using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public class DiamondPurchaseHistoryCell : MonoBehaviour
    {
        [SerializeField] UIText _dateText;
        [SerializeField] UIText _itemNameText;
        [SerializeField] UIText _amountText;
        [SerializeField] UIText _priceText;

        public void SetUpCell(DiamondPurchaseHistoryElementViewModel model)
        {
            _dateText.SetText(DateTimeOffsetFormatter.FormatDateTime(model.PurchaseAt.ToJst()));
            _itemNameText.SetText(model.ProductName.Value);
            _amountText.SetText("×{0}", model.Amount.ToStringSeparated());
            _priceText.SetText(model.Price.ToMoneyString());
        }
    }
}
