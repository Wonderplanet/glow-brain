using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.Logger
{
    public interface IInGameLogger
    {
        void Initialize();
        
        void AddDefeatEnemyCount(DefeatEnemyCount defeatEnemyCount, DefeatBossEnemyCount defeatBossEnemyCount);
        void ConcatDefeatEnemyCountDictionary(IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> dictionary);
        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> GetDefeatEnemyCountDictionary();
        void AddDiscoverEnemyId(MasterDataId mstEnemyCharacterId);
        void AddDiscoverEnemyIds(IReadOnlyList<MasterDataId> mstEnemyCharacterIds);
        void UpdateMaxDamage(Damage damage);
    }
}