using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessResult(
        IReadOnlyList<IAttackModel> RemovedAttacks,
        IReadOnlyList<IAttackModel> UpdatedAttacks,
        IReadOnlyList<CharacterUnitModel> UpdatedUnits,
        OutpostModel UpdatedPlayerOutpost,
        OutpostModel UpdatedEnemyOutpost,
        DefenseTargetModel UpdatedDefenseTarget,
        RushModel UpdatedRushModel,
        RushModel UpdatedPvpOpponentRushModel,
        IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults,
        IReadOnlyList<FieldObjectId> BlockedUnits,
        IReadOnlyList<FieldObjectId> SurvivedByGutsUnits,
        IReadOnlyList<AppliedDeckStateEffectResultModel> AppliedDeckStateEffectResultModels,
        IReadOnlyList<PlacedItemModel> UpdatedPlacedItems,
        IReadOnlyList<DeckUnitModel> UpdatedPlayerDeckUnits,
        IReadOnlyList<DeckUnitModel> UpdatedPvpOpponentDeckUnits)
    {
        public static AttackProcessResult Empty { get; } = new(
            new List<IAttackModel>(),
            new List<IAttackModel>(),
            new List<CharacterUnitModel>(),
            OutpostModel.Empty,
            OutpostModel.Empty,
            DefenseTargetModel.Empty,
            RushModel.Empty,
            RushModel.Empty,
            new List<AppliedAttackResultModel>(),
            new List<FieldObjectId>(),
            new List<FieldObjectId>(),
            new List<AppliedDeckStateEffectResultModel>(),
            new List<PlacedItemModel>(),
            new List<DeckUnitModel>(),
            new List<DeckUnitModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
