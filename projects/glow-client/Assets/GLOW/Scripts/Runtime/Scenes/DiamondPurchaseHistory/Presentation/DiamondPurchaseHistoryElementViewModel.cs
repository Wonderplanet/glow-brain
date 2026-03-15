using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public record DiamondPurchaseHistoryElementViewModel(
        PurchasePrice Price,
        PaidDiamond Amount,
        ProductName ProductName,
        DateTimeOffset PurchaseAt
    );
}
