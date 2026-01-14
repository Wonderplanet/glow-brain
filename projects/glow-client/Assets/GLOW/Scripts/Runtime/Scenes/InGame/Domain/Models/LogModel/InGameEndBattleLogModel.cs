using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.LogModel
{
    public record InGameEndBattleLogModel(
        DefeatEnemyCount DefeatEnemyCount,
        DefeatBossEnemyCount DefeatBossEnemyCount,
        InGameScore Score,
        IReadOnlyList<PartyStatusModel> PartyStatusModels,
        StageClearTime ClearTime,
        Damage MaxDamage,
        IReadOnlyList<MasterDataId> DiscoveredMstEnemyCharacterIds
    )
    {
        public static InGameEndBattleLogModel Empty { get; } = new(
            DefeatEnemyCount.Empty,
            DefeatBossEnemyCount.Empty,
            InGameScore.Empty,
            new List<PartyStatusModel>(),
            StageClearTime.Empty,
            Damage.Empty,
            new List<MasterDataId>()
        );
    };
}
