using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Shop
{
    public record PurchaseHistoryResultModel(IReadOnlyList<CurrencyPurchaseModel> CurrencyPurchaseModels);
}
