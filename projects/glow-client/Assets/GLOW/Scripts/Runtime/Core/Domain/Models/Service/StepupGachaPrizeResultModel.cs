using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record StepupGachaPrizeResultModel(
        IReadOnlyList<StepupGachaPrizeStepModel> StepupGachaPrizeStepModels)
    {
        public static StepupGachaPrizeResultModel Empty { get; } = new(
            new List<StepupGachaPrizeStepModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

