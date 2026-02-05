using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record UnitRemovingProcessResult(
        IReadOnlyList<CharacterUnitModel> RemovedUnits,         // 取り除かれたキャラ
        IReadOnlyList<CharacterUnitModel> DeadUnits,            // 取り除かれたキャラのうち、撃破されたキャラ
        IReadOnlyList<CharacterUnitModel> UpdatedUnits,         // 残ったキャラ
        IReadOnlyList<SpecialUnitModel> RemovedSpecialUnits,    // 取り除かれたロールがスペシャルのキャラ
        IReadOnlyList<SpecialUnitModel> UpdatedSpecialUnits,    // 残ったロールがスペシャルのキャラ
        DefeatEnemyCount DefeatEnemyCount,
        DefeatBossEnemyCount DefeatBossEnemyCount,
        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> DefeatEnemyCountDictionary)
    {
        public static UnitRemovingProcessResult Empty { get; } = new(
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            new List<SpecialUnitModel>(),
            new List<SpecialUnitModel>(),
            DefeatEnemyCount.Empty,
            DefeatBossEnemyCount.Empty,
            new Dictionary<MasterDataId, DefeatEnemyCount>());
    }
}
