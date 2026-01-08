using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record ResultSpeedAttackViewModel(
        StageClearTime ClearTime,
        IReadOnlyList<ResultSpeedAttackRewardViewModel> SpeedAttackRewards,
        NewRecordFlag IsNewRecord)
    {
        public static ResultSpeedAttackViewModel Empty { get; } = new (
            StageClearTime.Empty,
            Array.Empty<ResultSpeedAttackRewardViewModel>(),
            NewRecordFlag.Empty);
    }
}
