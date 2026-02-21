using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessApplyingRushResult(
        RushModel UpdatedRushModel,
        RushModel UpdatedPvpOpponentRushModel,
        List<AppliedDeckStateEffectResultModel> DeckStateEffectResultModels)
    {
        public static AttackProcessApplyingRushResult Empty { get; } = new(
            RushModel.Empty,
            RushModel.Empty,
            new List<AppliedDeckStateEffectResultModel>());
    }
}
