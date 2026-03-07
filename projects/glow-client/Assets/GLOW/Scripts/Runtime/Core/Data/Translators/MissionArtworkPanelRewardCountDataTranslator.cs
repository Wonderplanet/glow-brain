using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class MissionArtworkPanelRewardCountDataTranslator
    {
        public static MissionArtworkPanelRewardCountModel ToModel(MissionArtworkPanelRewardCountData data)
        {
            return new MissionArtworkPanelRewardCountModel(
                new MasterDataId(data.MstArtworkPanelMissionId),
                new UnreceivedMissionRewardCount(data.UnreceivedMissionRewardCount)
            );
        }
    }
}