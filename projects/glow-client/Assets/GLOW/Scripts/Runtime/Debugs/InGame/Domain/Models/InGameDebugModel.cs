#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.ValueObjects;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Models
{
    public record InGameDebugModel(
        bool IsZeroSummonCost,
        bool IsZeroSpecialAttackCoolTime,
        bool IsBattlePaused,
        DamageInvalidationFlag IsPlayerUnitDamageInvalidation,
        DamageInvalidationFlag IsEnemyUnitDamageInvalidation,
        OutpostDamageInvalidationFlag IsPlayerOutpostDamageInvalidation,
        OutpostDamageInvalidationFlag IsEnemyOutpostDamageInvalidation,
        IReadOnlyList<DebugEnemyInfoModel> DebugEnemyInfos,
        IReadOnlyList<DebugFieldUnitInfoModel> FieldUnitInfos,
        OutpostEnhancementModel OutpostEnhancement,
        TickCount StageTimeSpeed)
    {
        public static readonly InGameDebugModel Empty = new InGameDebugModel(
            false,
            false,
            false,
            DamageInvalidationFlag.False,
            DamageInvalidationFlag.False,
            OutpostDamageInvalidationFlag.False,
            OutpostDamageInvalidationFlag.False,
            new List<DebugEnemyInfoModel>(),
            new List<DebugFieldUnitInfoModel>(),
            OutpostEnhancementModel.Empty,
            TickCount.One
        );
    }
}
#endif // GLOW_INGAME_DEBUG

