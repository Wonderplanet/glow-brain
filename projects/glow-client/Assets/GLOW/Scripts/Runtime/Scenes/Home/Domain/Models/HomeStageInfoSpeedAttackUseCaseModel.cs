using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoSpeedAttackUseCaseModel(EventClearTimeMs ClearTimeMs, IReadOnlyList<HomeStageInfoSpeedAttackRewardUseCaseModel> ClearTimeRewards)
    {
        public static HomeStageInfoSpeedAttackUseCaseModel Empty { get; } = new(
            EventClearTimeMs.Empty,
            new List<HomeStageInfoSpeedAttackRewardUseCaseModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
