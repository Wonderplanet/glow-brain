using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprGachaUseResourceModel(
        MasterDataId OprGachaId,
        CostType CostType,
        MasterDataId MstCostId,
        CostAmount CostAmount,
        GachaDrawCount GachaDrawCount,
        GachaCostPriority GachaCostPriority
    )
    {
        public static OprGachaUseResourceModel Empty { get; } = new(MasterDataId.Empty, CostType.Coin, MasterDataId.Empty, CostAmount.Empty, GachaDrawCount.Zero, GachaCostPriority.Empty);
    }
}
