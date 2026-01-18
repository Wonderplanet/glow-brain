using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessApplyingUnitResult(
        IReadOnlyList<CharacterUnitModel> UpdatedUnits,
        IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults,
        IReadOnlyList<FieldObjectId> SurvivedByGutsUnits,
        IReadOnlyList<FieldObjectId> BlockedUnits);
}
