using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Core.Domain.Models.Shop
{
    public record CurrencyPurchaseModel(
        PurchasePrice PurchasePrice,
        PaidDiamond PurchasedAmount,
        CurrencyCode CurrencyCode,
        DateTimeOffset PurchaseAt
    );
}
