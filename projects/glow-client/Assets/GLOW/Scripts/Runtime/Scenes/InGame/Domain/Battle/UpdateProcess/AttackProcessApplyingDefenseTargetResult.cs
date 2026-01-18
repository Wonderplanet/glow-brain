using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessApplyingDefenseTargetResult(
        DefenseTargetModel DefenseTarget,
        IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults)
    {
        public static AttackProcessApplyingDefenseTargetResult Empty { get; } = new(
            DefenseTargetModel.Empty,
            new List<AppliedAttackResultModel>());
    }
}
