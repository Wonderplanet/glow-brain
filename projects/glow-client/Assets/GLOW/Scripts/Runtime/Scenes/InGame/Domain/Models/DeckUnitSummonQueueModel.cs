using System.Collections.Generic;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record DeckUnitSummonQueueModel(IReadOnlyList<DeckUnitSummonQueueElement> SummonQueue)
    {
        public static DeckUnitSummonQueueModel Empty { get; } = new(new List<DeckUnitSummonQueueElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
