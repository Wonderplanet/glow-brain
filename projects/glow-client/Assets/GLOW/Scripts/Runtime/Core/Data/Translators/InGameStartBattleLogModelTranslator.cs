using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Data.Translators
{
    public class InGameStartBattleLogModelTranslator
    {
        public static InGameStartBattleLogData ToInGameStartBattleLogData(InGameStartBattleLogModel inGameLogModel)
        {
            return new InGameStartBattleLogData()
            {
                PartyStatus = inGameLogModel.PartyStatusModels.Select(PartyStatusDataModelTranslator.ToPartyStatusData).ToArray()
            };
        }


    }

}
