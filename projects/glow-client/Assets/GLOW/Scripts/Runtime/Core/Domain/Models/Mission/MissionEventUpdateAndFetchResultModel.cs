using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionEventUpdateAndFetchResultModel(IReadOnlyList<MissionEventModel> MissionEventModels)
    {
        public static MissionEventUpdateAndFetchResultModel Empty { get; } = new(new List<MissionEventModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}