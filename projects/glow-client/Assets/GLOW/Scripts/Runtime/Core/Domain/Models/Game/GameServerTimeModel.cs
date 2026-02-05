using System;

namespace GLOW.Core.Domain.Models
{
    public record GameServerTimeModel(DateTimeOffset ServerTime)
    {
        public DateTimeOffset ServerTime { get; } = ServerTime;
    }
}
