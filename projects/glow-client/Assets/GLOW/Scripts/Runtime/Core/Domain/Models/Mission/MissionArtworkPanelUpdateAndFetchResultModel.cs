using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionArtworkPanelUpdateAndFetchResultModel(
        IReadOnlyList<UserMissionLimitedTermModel> UserMissionLimitedTermModels,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragmentModels)
    {
        public static MissionArtworkPanelUpdateAndFetchResultModel Empty { get; } = new(
            new List<UserMissionLimitedTermModel>(),
            new List<UserArtworkFragmentModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}