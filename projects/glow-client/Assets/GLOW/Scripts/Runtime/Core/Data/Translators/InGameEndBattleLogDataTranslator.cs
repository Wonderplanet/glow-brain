using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Data.Translators
{
    public class InGameEndBattleLogDataTranslator
    {
        public static InGameEndBattleLogData ToInGameEndBattleLogData(
            InGameEndBattleLogModel inGameLogModel)
        {
            var partyStatusList = inGameLogModel.PartyStatusModels
                .Select(PartyStatusDataModelTranslator.ToPartyStatusData)
                .ToArray();
            
            var discoveredEnemyDataList = inGameLogModel.DiscoveredMstEnemyCharacterIds
                .GroupBy(id => id)
                .Select(group => new DiscoveredEnemyData()
                {
                    MstEnemyCharacterId = group.Key.Value,
                    Count = group.Count()
                })
                .ToArray();
            
            return new InGameEndBattleLogData()
            {
                DefeatEnemyCount = inGameLogModel.DefeatEnemyCount.Value,
                DefeatBossEnemyCount = inGameLogModel.DefeatBossEnemyCount.Value,
                Score = inGameLogModel.Score.Value,
                ClearTimeMs = (int)inGameLogModel.ClearTime.ToMilliSeconds(),
                PartyStatus = partyStatusList,
                MaxDamage = inGameLogModel.MaxDamage.Value,
                DiscoveredEnemies = discoveredEnemyDataList
            };
        }
    }
}
