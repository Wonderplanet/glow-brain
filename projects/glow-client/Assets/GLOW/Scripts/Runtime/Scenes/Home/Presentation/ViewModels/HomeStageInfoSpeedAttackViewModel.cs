using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeStageInfoSpeedAttackViewModel(
        EventClearTimeMs ClearTime,
        IReadOnlyList<PlayerResourceIconViewModel> ClearTimeRewards)
    {
        public static HomeStageInfoSpeedAttackViewModel Empty { get; } = new (
            EventClearTimeMs.Empty,
            new List<PlayerResourceIconViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
