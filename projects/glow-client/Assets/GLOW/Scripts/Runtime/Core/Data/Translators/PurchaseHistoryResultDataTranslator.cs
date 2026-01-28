using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Core.Data.Translators
{
    public class PurchaseHistoryResultDataTranslator
    {
        public static PurchaseHistoryResultModel ToPurchaseHistoryResultModel(
            PurchaseHistoryResultData purchaseHistoryResultData)
        {
            var models = purchaseHistoryResultData.CurrencyPurchases
                .Select(ToCurrencyPurchaseModel)
                .ToList();
            return new PurchaseHistoryResultModel(models);
        }

        static CurrencyPurchaseModel ToCurrencyPurchaseModel(CurrencyPurchaseData data)
        {
            return new CurrencyPurchaseModel(
                new PurchasePrice(data.PurchasePrice),
                new PaidDiamond(data.PurchaseAmount),
                new CurrencyCode(data.CurrencyCode),
                data.PurchaseAt
            );
        }
    }
}
