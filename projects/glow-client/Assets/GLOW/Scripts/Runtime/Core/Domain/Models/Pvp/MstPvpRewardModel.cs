using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpRewardModel(
        MasterDataId Id,
        MasterDataId MstPvpRewardGroupId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount Amount)
    {
        public static MstPvpRewardModel Empty { get; } = new MstPvpRewardModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}