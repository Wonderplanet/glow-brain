using System;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionEndDate(DateTimeOffset Value)
    {
        public static MissionEndDate Empty { get; } = new(DateTimeOffset.MinValue);
    }
}