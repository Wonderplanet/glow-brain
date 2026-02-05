using System;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpTopApiCallAllowedStatus(bool ApiCallAllowed, DateTimeOffset UpdatedAt)
    {
        public static PvpTopApiCallAllowedStatus Empty { get; } = new PvpTopApiCallAllowedStatus(true, DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsApiCallAllowed()
        {
            return IsEmpty() || ApiCallAllowed;
        }
    };
}
