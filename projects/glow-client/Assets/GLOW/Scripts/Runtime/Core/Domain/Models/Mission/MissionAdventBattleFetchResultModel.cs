using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionAdventBattleFetchResultModel(
        IReadOnlyList<UserMissionEventModel> UserMissionEventModels,
        IReadOnlyList<UserMissionLimitedTermModel> UserMissionLimitedTermModels)
    {
        public static MissionAdventBattleFetchResultModel Empty { get; } = new(
            new List<UserMissionEventModel>(),
            new List<UserMissionLimitedTermModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}