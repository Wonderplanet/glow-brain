using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Data.Translators
{
    public class PvpInGameEndBattleLogDataTranslator
    {
        public static PvpInGameEndBattleLogData ToPvpInGameEndBattleLogData(
            PvpInGameEndBattleLogModel pvpInGameLogModel)
        {
            var partyStatusList = pvpInGameLogModel.PlayerPartyStatusModels
                .Select(PartyStatusDataModelTranslator.ToPartyStatusData)
                .ToArray();
            
            var opponentPartyStatusList = pvpInGameLogModel.OpponentPartyStatusModels
                .Select(PartyStatusDataModelTranslator.ToPartyStatusData)
                .ToArray();
            
            return new PvpInGameEndBattleLogData()
            {
                ClearTimeMs = pvpInGameLogModel.ClearTime.ToMilliSeconds(),
                MaxDamage = pvpInGameLogModel.MaxDamage.Value,
                PartyStatus = partyStatusList,
                OpponentPartyStatus = opponentPartyStatusList,
            };
        }
    }
}