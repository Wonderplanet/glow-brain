using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IResultScoreModelFactory
    {
        public ResultScoreModel CreateResultScoreModel(
            GameFetchModel prevFetchModel,
            MstQuestModel mstQuest,
            IReadOnlyList<MasterDataId> oprCampaignIds);
    }
}
