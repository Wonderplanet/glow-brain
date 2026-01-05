using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.Logger
{
    public class InGameLogger : IInGameLogger
    {
        [Inject] IInGameLogRepository InGameLogRepository { get; }
        
        public void Initialize()
        {
            InGameLogRepository.SetLog(InGameLogModel.Empty);
        }
        
        public void AddDefeatEnemyCount(DefeatEnemyCount defeatEnemyCount, DefeatBossEnemyCount defeatBossEnemyCount)
        {
            if (defeatBossEnemyCount.IsZero() && defeatEnemyCount.IsZero())
            {
                return;
            }
            
            var inGameLogModel = InGameLogRepository.GetLog();
            
            var updatedInGameLogModel = inGameLogModel with
            {
                DefeatEnemyCount = inGameLogModel.DefeatEnemyCount + defeatEnemyCount,
                DefeatBossEnemyCount = inGameLogModel.DefeatBossEnemyCount + defeatBossEnemyCount
            };
            
            InGameLogRepository.SetLog(updatedInGameLogModel);
        }

        public void ConcatDefeatEnemyCountDictionary(IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> dictionary)
        {
            if (!dictionary.Any())
            {
                return;
            }
            
            var inGameLogModel = InGameLogRepository.GetLog();

            var updatedDictionary = new Dictionary<MasterDataId, DefeatEnemyCount>(inGameLogModel.DefeatEnemyCountDictionary);
            
            foreach (var kvp in dictionary)
            {
                if (updatedDictionary.TryGetValue(kvp.Key, out var existingCount))
                {
                    updatedDictionary[kvp.Key] = existingCount + kvp.Value;
                }
                else
                {
                    updatedDictionary[kvp.Key] = kvp.Value;
                }
            }

            var updatedInGameLogModel = inGameLogModel with
            {
                DefeatEnemyCountDictionary = updatedDictionary
            };
            
            InGameLogRepository.SetLog(updatedInGameLogModel);
        }
        
        public IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> GetDefeatEnemyCountDictionary()
        {
            var inGameLogModel = InGameLogRepository.GetLog();
            return inGameLogModel.DefeatEnemyCountDictionary;
        }

        public void AddDiscoverEnemyId(MasterDataId mstEnemyCharacterId)
        {
            var inGameLogModel = InGameLogRepository.GetLog();

            var updatedDiscoveredIds = inGameLogModel.DiscoveredMstEnemyCharacterIds
                .Append(mstEnemyCharacterId)
                .ToList();

            var updatedInGameLogModel = inGameLogModel with
            {
                DiscoveredMstEnemyCharacterIds = updatedDiscoveredIds
            };
            
            InGameLogRepository.SetLog(updatedInGameLogModel);
        }

        public void AddDiscoverEnemyIds(IReadOnlyList<MasterDataId> mstEnemyCharacterIds)
        {
            var inGameLogModel = InGameLogRepository.GetLog();

            var updatedDiscoveredIds = inGameLogModel.DiscoveredMstEnemyCharacterIds
                .Concat(mstEnemyCharacterIds)
                .ToList();

            var updatedInGameLogModel = inGameLogModel with
            {
                DiscoveredMstEnemyCharacterIds = updatedDiscoveredIds
            };
            
            InGameLogRepository.SetLog(updatedInGameLogModel);
        }

        public void UpdateMaxDamage(Damage damage)
        {
            var inGameLogModel = InGameLogRepository.GetLog();

            if (damage <= inGameLogModel.MaxDamage) return;
            
            var updatedInGameLogModel = inGameLogModel with
            {
                MaxDamage = damage
            };
            
            InGameLogRepository.SetLog(updatedInGameLogModel);
        }
    }
}