using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackBaseData(
            IReadOnlyList<CharacterColor> KillerColors,
            KillerPercentage KillerPercentage,
            TickCount ActionDuration,
            TickCount AttackInterval)
    {
        public static AttackBaseData Empty { get; } = new(
            Array.Empty<CharacterColor>(),
            KillerPercentage.Empty,
            TickCount.Empty,
            TickCount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
