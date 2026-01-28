using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

#if GLOW_INGAME_DEBUG
namespace GLOW.Debugs.InGame.Domain.Models
{
    public record InGameDebugSettingModel(
        bool IsSkipApi,
        bool IsOverrideUnits,
        bool IsOverrideSummons,
        IReadOnlyList<UnitAssetKey> OverrideUnitAssetKeys,
        IReadOnlyList<MstEnemyStageParameterModel> OverrideSummonParameters)
    {
        public static InGameDebugSettingModel Empty =>
            new InGameDebugSettingModel(
            false,
            false,
            false,
            new List<UnitAssetKey>(),
            new List<MstEnemyStageParameterModel>());
    }
}
#endif
