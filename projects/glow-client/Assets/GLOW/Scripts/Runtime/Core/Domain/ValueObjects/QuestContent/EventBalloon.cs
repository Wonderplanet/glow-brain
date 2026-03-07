using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventBalloon(
        EventUnitStandImageAssetPath EventUnitStandImageAssetPath)
    {
        public static EventBalloon Empty { get; } = new EventBalloon(EventUnitStandImageAssetPath.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
