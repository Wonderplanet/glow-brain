using System.Collections.Generic;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitSummonQueueModel(IReadOnlyList<UnitSummonQueueElement> SummonQueue)
    {
        public static UnitSummonQueueModel Empty { get; } = new(new List<UnitSummonQueueElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
