using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestSelectBadgeModel(
        NotificationBadge IsExistReceivableMission,
        BoxGachaDrawableFlag IsBoxGachaDrawable)
    {
        public static EventQuestSelectBadgeModel Empty { get; } = new(
            NotificationBadge.False,
            BoxGachaDrawableFlag.False
        );
    }
}