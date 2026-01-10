using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleAbortResultModelTranslator
    {
        public static AdventBattleAbortResultModel ToAdventBattleAbortResultModel(AdventBattleAbortResultData data)
        {
            return new AdventBattleAbortResultModel(new AdventBattleRaidTotalScore(data.TotalDamage));
        }
    }
}