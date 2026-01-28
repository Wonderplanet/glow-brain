using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public record EventQuestSelectBadgeViewModel(
        NotificationBadge IsExistReceivableMission,
        BoxGachaDrawableFlag IsBoxGachaDrawable)
    {
        public static EventQuestSelectBadgeViewModel Empty { get; } = new(
            NotificationBadge.False,
            BoxGachaDrawableFlag.False
        );
    }
}