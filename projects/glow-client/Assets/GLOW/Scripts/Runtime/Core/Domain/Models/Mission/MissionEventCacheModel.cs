using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionEventCacheModel(
        Dictionary<MasterDataId, MissionEventModel> MissionEventDictionary)
    {
        public static MissionEventCacheModel Empty { get; } = new(new Dictionary<MasterDataId, MissionEventModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}