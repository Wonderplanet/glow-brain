using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.LogModel
{
    public record InGameLogModel(
        DefeatEnemyCount DefeatEnemyCount,
        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> DefeatEnemyCountDictionary,
        DefeatBossEnemyCount DefeatBossEnemyCount,
        IReadOnlyList<MasterDataId> DiscoveredMstEnemyCharacterIds,
        Damage MaxDamage)
    {
        public static InGameLogModel Empty { get; } = new(
            DefeatEnemyCount.Empty,
            new Dictionary<MasterDataId, DefeatEnemyCount>(),
            DefeatBossEnemyCount.Empty,
            new List<MasterDataId>(),
            Damage.Empty);
            
        public virtual bool Equals(InGameLogModel other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other == null) return false;

            if (DefeatEnemyCount != other.DefeatEnemyCount) return false;
            if (DefeatBossEnemyCount != other.DefeatBossEnemyCount) return false;
            if (MaxDamage != other.MaxDamage) return false;
            
            if ((DefeatEnemyCountDictionary == null) ^ (other.DefeatEnemyCountDictionary == null)) return false;
            if (DefeatEnemyCountDictionary != null && other.DefeatEnemyCountDictionary != null)
            {
                if (!DefeatEnemyCountDictionary.SequenceEqual(other.DefeatEnemyCountDictionary)) return false;
            }

            if ((DiscoveredMstEnemyCharacterIds == null) ^ (other.DiscoveredMstEnemyCharacterIds == null)) return false;
            if (DiscoveredMstEnemyCharacterIds != null && other.DiscoveredMstEnemyCharacterIds != null)
            {
                if (!DiscoveredMstEnemyCharacterIds.SequenceEqual(other.DiscoveredMstEnemyCharacterIds)) return false;
            }

            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();

            hash.Add(DefeatEnemyCount);
            AddHashCodes(hash, DefeatEnemyCountDictionary);
            hash.Add(DefeatBossEnemyCount);
            AddHashCodes(hash, DiscoveredMstEnemyCharacterIds);
            hash.Add(MaxDamage);

            return hash.ToHashCode();
        }
        
        static void AddHashCodes<T>(HashCode hash, IReadOnlyList<T> models)
        {
            if (models == null) return;

            int start = models.Count > 10 ? models.Count - 10 : 0;
            for (int i = start; i < models.Count; i++)
            {
                hash.Add(models[i]);
            }
        }
        
        static void AddHashCodes<T1, T2>(HashCode hash, IReadOnlyDictionary<T1, T2> dictionary)
        {
            if (dictionary == null) return;

            var keys = dictionary.Keys.ToList();
            int start = keys.Count > 10 ? keys.Count - 10 : 0;
            for (int i = start; i < keys.Count; i++)
            {
                hash.Add(keys[i]);
                hash.Add(dictionary[keys[i]]);
            }
        }
    }
}
