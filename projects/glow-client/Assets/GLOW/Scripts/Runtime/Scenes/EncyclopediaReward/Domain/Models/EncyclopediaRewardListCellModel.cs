using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaReward.Domain.Models
{
    public record EncyclopediaRewardListCellModel(
        MasterDataId MstEncyclopediaRewardId,
        PlayerResourceModel RewardItem,
        EncyclopediaUnitGrade RequireGrade,
        UnitEncyclopediaEffectType EffectType,
        UnitEncyclopediaEffectValue EffectValue,
        NotificationBadge Badge,
        ReceivedFlag IsReceived);
}
