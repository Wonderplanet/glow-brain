using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public interface ICommonConditionContext
    {
        CharacterUnitModel MyUnit { get; }
        IReadOnlyList<CharacterUnitModel> Units { get; }
        IReadOnlyList<CharacterUnitModel> DeadUnits { get; }
        DefeatEnemyCount TotalDeadEnemyCount { get; }
        OutpostModel PlayerOutpost { get; }
        OutpostModel EnemyOutpost { get; }
        StageTimeModel StageTime { get; }
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary { get; }
        MstPageModel MstPage { get; }
        AutoPlayerSequenceGroupModel CurrentSequenceGroupModel { get; }
    }
}