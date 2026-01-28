using System.Collections.Generic;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialUnitSummonQueueModel(IReadOnlyList<SpecialUnitSummonQueueElement> SummonQueue)
    {
        public static SpecialUnitSummonQueueModel Empty { get; } = new(new List<SpecialUnitSummonQueueElement>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
