using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessApplyingOutpostResult(
        OutpostModel Outpost,
        IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults)
    {
        public static AttackProcessApplyingOutpostResult Empty { get; } = new(
            OutpostModel.Empty,
            new List<AppliedAttackResultModel>());
    }
}
