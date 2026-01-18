using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class UserAdventBattleModelTranslator
    {
        public static UserAdventBattleModel ToUserAdventBattleModel(UsrAdventBattleData data)
        {
            return new UserAdventBattleModel(
                new MasterDataId(data.MstAdventBattleId),
                new AdventBattleScore(data.MaxScore),
                new AdventBattleScore(data.TotalScore),
                new AdventBattleScore(data.MaxReceivedMaxScoreReward),
                new AdventBattleChallengeCount(data.ResetChallengeCount),
                new AdventBattleChallengeCount(data.ResetAdChallengeCount),
                new StageClearCount(data.ClearCount));
        }
    }
}
