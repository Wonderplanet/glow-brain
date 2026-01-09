using System.Collections.Generic;

namespace GLOW.Scenes.DiamondPurchaseHistory.Domain
{
    public record DiamondPurchaseHistoryUseCaseModel(
        IReadOnlyList<DiamondPurchaseHistoryElementUseCaseModel> Elements);
}
