using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaResultModel(
        RewardModel RewardModel,
        GachaPrizeType GachaPrizeType);
}
