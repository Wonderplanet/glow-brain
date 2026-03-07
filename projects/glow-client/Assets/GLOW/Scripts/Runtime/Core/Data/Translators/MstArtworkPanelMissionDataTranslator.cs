using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class MstArtworkPanelMissionDataTranslator
    {
        public static MstArtworkPanelMissionModel ToModel(MstArtworkPanelMissionData data)
        {
            return new MstArtworkPanelMissionModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstArtworkId),
                new MasterDataId(data.MstEventId),
                new MasterDataId(data.InitialOpenMstArtworkFragmentId),
                new MstArtworkPanelMissionStartDate(data.StartAt),
                new MstArtworkPanelMissionEndDate(data.EndAt)
            );
        }
    }
}