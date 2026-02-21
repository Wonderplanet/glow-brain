using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record ResultSpeedAttackModel(
        StageClearTime ClearTime,
        IReadOnlyList<ResultSpeedAttackRewardModel> SpeedAttackRewards,
        NewRecordFlag IsNewRecord)
    {
        public static ResultSpeedAttackModel Empty { get; } = new (
            StageClearTime.Empty,
            Array.Empty<ResultSpeedAttackRewardModel>(),
            NewRecordFlag.Empty
        );
    }
}
