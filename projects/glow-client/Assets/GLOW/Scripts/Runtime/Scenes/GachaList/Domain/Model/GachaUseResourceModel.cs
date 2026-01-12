using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaUseResourceModel(
        CostType CostType,
        MasterDataId GachaId,
        MasterDataId ItemId,
        CostAmount CostAmount,
        GachaDrawCount GachaDrawCount,
        GachaCostPriority GachaCostPriority)
    {
        public static GachaUseResourceModel Empty { get; } = new(
            CostType.Free,
            MasterDataId.Empty,
            MasterDataId.Empty,
            new CostAmount(0),
            new GachaDrawCount(0),
            GachaCostPriority.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
