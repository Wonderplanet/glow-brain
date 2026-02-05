using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaHistoryRewardModel(
        SortOrder SortOrder,
        RewardModel RewardModel);
}