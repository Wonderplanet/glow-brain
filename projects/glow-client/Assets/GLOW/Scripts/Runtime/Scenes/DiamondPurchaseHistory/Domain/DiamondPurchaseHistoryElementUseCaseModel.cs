using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.DiamondPurchaseHistory.Domain
{
    public record DiamondPurchaseHistoryElementUseCaseModel(
        PurchasePrice Price,
        PaidDiamond Amount,
        ProductName ProductName,
        DateTimeOffset PurchaseAt
    );
}
