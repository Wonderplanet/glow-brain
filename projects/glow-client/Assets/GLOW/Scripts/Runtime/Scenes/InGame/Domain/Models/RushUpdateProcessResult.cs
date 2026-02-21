using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record RushUpdateProcessResult(
        RushModel RushModel,
        RushModel PvpOpponentRushModel,
        IReadOnlyList<IAttackModel> UpdatedAttacks,
        AttackPower CalculatedPlayerRushDamage,
        RushEvaluationType RushEvaluationType,
        IReadOnlyList<MasterDataId> UpdatedUsedSpecialUnitIdsBeforeNextRush)
    {
        public static RushUpdateProcessResult Empty { get; } = new(
            RushModel.Empty,
            RushModel.Empty,
            Array.Empty<IAttackModel>(),
            AttackPower.Empty,
            RushEvaluationType.Good,
            new List<MasterDataId>());
    }
}
