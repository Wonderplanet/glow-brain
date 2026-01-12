using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record CommonConditionContext(
        CharacterUnitModel MyUnit,
        IReadOnlyList<CharacterUnitModel> Units,
        IReadOnlyList<CharacterUnitModel> DeadUnits,
        DefeatEnemyCount TotalDeadEnemyCount,
        OutpostModel PlayerOutpost,
        OutpostModel EnemyOutpost,
        StageTimeModel StageTime,
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        MstPageModel MstPage,
        AutoPlayerSequenceGroupModel CurrentSequenceGroupModel) : ICommonConditionContext
    {
        public static CommonConditionContext Empty { get; } = new (
            CharacterUnitModel.Empty,
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            DefeatEnemyCount.Empty,
            OutpostModel.Empty,
            OutpostModel.Empty,
            StageTimeModel.Empty,
            new Dictionary<KomaId, KomaModel>(),
            MstPageModel.Empty,
            AutoPlayerSequenceGroupModel.Empty
            );

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
