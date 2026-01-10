using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.AdventBattleMission.Domain.Model
{
    public record AdventBattleMissionAppliedModel(
        IReadOnlyList<UserMissionEventModel> UserMissionEventModels, 
        IReadOnlyList<UserMissionLimitedTermModel> UserMissionLimitedTermModels);
}