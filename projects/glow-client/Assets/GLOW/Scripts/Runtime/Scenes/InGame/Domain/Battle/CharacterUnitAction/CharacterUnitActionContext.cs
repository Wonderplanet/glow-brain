using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public record CharacterUnitActionContext(
        CharacterUnitModel CharacterUnit,
        IReadOnlyList<CharacterUnitModel> CharacterUnits,
        IReadOnlyList<CharacterUnitModel> DeadUnits,
        DefeatEnemyCount TotalDeadEnemyCount,
        IReadOnlyList<IAttackTargetModel> AttackTargetCandidates,
        IReadOnlyList<IAttackTargetModel> SortedPlayerAttackTargetCandidates,
        IReadOnlyList<IAttackTargetModel> SortedEnemyAttackTargetCandidates,
        OutpostModel PlayerOutpost,
        OutpostModel EnemyOutpost,
        DefenseTargetModel DefenseTargetModel,
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        ICoordinateConverter CoordinateConverter,
        IAttackModelFactory AttackModelFactory,
        IStateEffectModelFactory StateEffectModelFactory,
        MstPageModel MstPage,
        StageTimeModel StageTime,
        TickCount TickCount,
        AutoPlayerSequenceGroupModel EnemyCurrentSequenceGroupModel,
        IStateEffectChecker StateEffectChecker,
        IBuffStatePercentageConverter BuffStatePercentageConverter,
        INearestTargetFinder NearestTargetFinder)
    {
        public static CharacterUnitActionContext Empty { get; } = new(
            CharacterUnitModel.Empty,
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            DefeatEnemyCount.Empty,
            new List<IAttackTargetModel>(),
            new List<IAttackTargetModel>(),
            new List<IAttackTargetModel>(),
            OutpostModel.Empty,
            OutpostModel.Empty,
            DefenseTargetModel.Empty,
            new Dictionary<KomaId, KomaModel>(),
            null,
            null,
            null,
            MstPageModel.Empty,
            StageTimeModel.Empty,
            TickCount.Zero,
            AutoPlayerSequenceGroupModel.Empty,
            null,
            null,
            null);
    }
}
