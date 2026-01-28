using System;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventBalloon(
        SeriesLogoImagePath SeriesLogoImagePath,
        EventUnitStandImageAssetPath EventUnitStandImageAssetPath)
    {
        public static EventBalloon Empty { get; } = new EventBalloon(SeriesLogoImagePath.Empty, EventUnitStandImageAssetPath.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
