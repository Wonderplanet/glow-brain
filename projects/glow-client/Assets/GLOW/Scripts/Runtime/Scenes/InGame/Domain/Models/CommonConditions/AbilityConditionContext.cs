using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record AbilityConditionContext(CharacterUnitModel MyUnit) : ICommonConditionContext
    {
        public IReadOnlyList<CharacterUnitModel> Units { get; } = new List<CharacterUnitModel>();
        public IReadOnlyList<CharacterUnitModel> DeadUnits { get; } = new List<CharacterUnitModel>();
        public DefeatEnemyCount TotalDeadEnemyCount { get; } = DefeatEnemyCount.Empty;
        public OutpostModel PlayerOutpost { get; } = OutpostModel.Empty;
        public OutpostModel EnemyOutpost { get; } = OutpostModel.Empty;
        public StageTimeModel StageTime { get; } = StageTimeModel.Empty;
        public IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary { get; } = new Dictionary<KomaId, KomaModel>();
        public MstPageModel MstPage { get; } = MstPageModel.Empty;
        public AutoPlayerSequenceGroupModel CurrentSequenceGroupModel { get; } = AutoPlayerSequenceGroupModel.Empty;
    }
}