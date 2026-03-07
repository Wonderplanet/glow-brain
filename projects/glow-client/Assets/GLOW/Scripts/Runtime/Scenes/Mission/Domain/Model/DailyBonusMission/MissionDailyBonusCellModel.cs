using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.DailyBonusMission
{
    public record MissionDailyBonusCellModel(
        MasterDataId MissionDailyBonusId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        LoginDayCount LoginDayCount,
        IReadOnlyList<PlayerResourceModel> PlayerResourceModels);
}
