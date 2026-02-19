using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Data.Data
{
    public record InGameLogData(
        int DefeatEnemyCount,
        int DefeatBossEnemyCount,
        IReadOnlyList<MasterDataId> DiscoveredMstEnemyCharacterIds,
        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> DefeatEnemyCountDictionary)
    {
        public static readonly InGameLogData Empty =
            new InGameLogData(0,
                0,
                new List<MasterDataId>(),
                new Dictionary<MasterDataId, DefeatEnemyCount>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
