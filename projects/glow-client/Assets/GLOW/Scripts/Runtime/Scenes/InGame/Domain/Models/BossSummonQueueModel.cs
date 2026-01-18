using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BossSummonQueueModel(
        BossSummonQueueElement NextBoss,
        IReadOnlyList<BossSummonQueueElement> SummonQueue)
    {
        public static BossSummonQueueModel Empty { get; } = new(
            BossSummonQueueElement.Empty,
            Array.Empty<BossSummonQueueElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool ExistsQueuedBoss()
        {
            return !NextBoss.IsEmpty() || SummonQueue.Count > 0;
        }
    }
}
