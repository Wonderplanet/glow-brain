using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattleMission.Domain.Model;

namespace GLOW.Scenes.AdventBattleMission.Domain.Applier
{
    public interface IAdventBattleMissionReceivedStatusApplier
    {
        AdventBattleMissionAppliedModel UpdateReceivedAdventBattleMission(MissionType missionType, MasterDataId receivedMissionId);
        
        AdventBattleMissionAppliedModel UpdateReceivedAdventBattleMissions(IReadOnlyList<MissionReceiveRewardModel> receivedMissionModels);
    }
}