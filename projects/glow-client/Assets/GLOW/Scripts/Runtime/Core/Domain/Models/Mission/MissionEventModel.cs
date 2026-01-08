using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionEventModel(
        MasterDataId MstEventId,
        List<UserMissionEventModel> UserMissionEventModels)
    {
        public static MissionEventModel Empty { get; } = new(
            MasterDataId.Empty,
            new List<UserMissionEventModel>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);

    }
}
