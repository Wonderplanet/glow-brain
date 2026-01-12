using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels
{
    public record EncyclopediaRewardListCellViewModel(
        MasterDataId MstEncyclopediaRewardId,
        PlayerResourceIconViewModel RewardItem,
        EncyclopediaUnitGrade RequireGrade,
        UnitEncyclopediaEffectType EffectType,
        UnitEncyclopediaEffectValue EffectValue,
        NotificationBadge Badge,
        ReceivedFlag IsReceived);
}
