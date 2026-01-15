using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleTopResultModelTranslator
    {
        public static AdventBattleTopResultModel ToAdventBattleTopResultModel(AdventBattleTopResultData data)
        {
            return new AdventBattleTopResultModel(
                data.AdventBattleMaxScoreRewards.Select(AdventBattleRewardModelTranslator.ToAdventBattleRewardModel).ToList(),
                data.AdventBattleRaidTotalScoreRewards.Select(AdventBattleRewardModelTranslator.ToAdventBattleRewardModel).ToList(),
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                data.UsrItems?.Select(ItemDataTranslator.ToUserItemModel).ToList(),
                data.UsrEmblems?.Select(UserEmblemDataTranslator.ToUserEmblemModel).ToList());
        }
    }
}