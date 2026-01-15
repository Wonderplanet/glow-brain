using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleClearRewardModel(
        AdventBattleClearRewardCategory RewardCategory,
        UnreceivedRewardReasonType UnreceivedRewardReasonType,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        PlayerResourceAmount ResourceAmount,
        PreConversionResourceModel PreConversionResource)
    {
        public static AdventBattleClearRewardModel Empty { get; } = new(
            AdventBattleClearRewardCategory.FirstClear,
            UnreceivedRewardReasonType.None,
            ResourceType.Coin,
            MasterDataId.Empty,
            PlayerResourceAmount.Empty,
            PreConversionResourceModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
