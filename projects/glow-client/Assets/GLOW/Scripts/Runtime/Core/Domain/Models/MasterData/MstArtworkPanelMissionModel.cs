using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkPanelMissionModel(
        MasterDataId Id,
        MasterDataId MstArtworkId,
        MasterDataId MstEventId,
        MasterDataId InitialOpenMstArtworkFragmentId,
        MstArtworkPanelMissionStartDate StartDate,
        MstArtworkPanelMissionEndDate EndDate)
    {
        public static MstArtworkPanelMissionModel Empty { get; } = new MstArtworkPanelMissionModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            new MstArtworkPanelMissionStartDate(DateTimeOffset.MinValue),
            new MstArtworkPanelMissionEndDate(DateTimeOffset.MaxValue));
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}