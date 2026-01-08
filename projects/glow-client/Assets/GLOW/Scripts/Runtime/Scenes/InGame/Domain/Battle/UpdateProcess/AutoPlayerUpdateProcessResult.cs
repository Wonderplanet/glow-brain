using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AutoPlayerUpdateProcessResult(
        IReadOnlyList<CharacterUnitModel> SummonedUnitList,
        IReadOnlyList<CharacterUnitModel> UpdatedUnitList,
        IReadOnlyList<GimmickObjectToEnemyTransformationModel> UpdatedGimmickObjectToEnemyTransformationModels,
        UnitSummonQueueModel UpdatedUnitSummonQueue,
        BossSummonQueueModel UpdatedBossSummonQueue,
        DeckUnitSummonQueueModel UpdatedDeckUnitSummonQueue,
        SpecialUnitSummonQueueModel UpdatedSpecialUnitSummonQueue,
        IReadOnlyList<DeckUnitModel> UpdatedDeckUnitList,
        IReadOnlyList<DeckUnitModel> UpdatedOpponentDeckUnitList,
        BattlePointModel UpdatedBattlePointModel,
        BattlePointModel UpdatedPvpOpponentBattlePointModel,
        RushModel UpdatePvpOpponentRushModel)
    {
        public static AutoPlayerUpdateProcessResult Empty { get; } = new AutoPlayerUpdateProcessResult(
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            new List<GimmickObjectToEnemyTransformationModel>(),
            UnitSummonQueueModel.Empty,
            BossSummonQueueModel.Empty,
            DeckUnitSummonQueueModel.Empty,
            SpecialUnitSummonQueueModel.Empty,
            new List<DeckUnitModel>(),
            new List<DeckUnitModel>(),
            BattlePointModel.Empty,
            BattlePointModel.Empty,
            RushModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
