using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleInfoResultModelTranslator
    {
        public static AdventBattleInfoResultModel ToAdventBattleInfoResultModel(AdventBattleInfoResultData data)
        {
            return new AdventBattleInfoResultModel(ToAdventBattleResultModel(data.AdventBattleResult));
        }

        static AdventBattleResultModel ToAdventBattleResultModel(AdventBattleResultData data)
        {
            if (data == null)
            {
                return AdventBattleResultModel.Empty;
            }

            return new AdventBattleResultModel(
                new MasterDataId(data.MstAdventBattleId),
                AdventBattleRankingResultModelTranslator.ToAdventBattleMyRankingModel(data.MyRanking),
                new AdventBattleScore(data.TotalDamage));
        }
    }
}