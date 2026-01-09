using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record UserAdventBattleModel(
        MasterDataId MstAdventBattleId,
        AdventBattleScore MaxScore,
        AdventBattleScore TotalScore,
        AdventBattleScore HighScoreLastAnimationPlayed,
        AdventBattleChallengeCount ResetChallengeCount,
        AdventBattleChallengeCount ResetAdChallengeCount,
        StageClearCount ClearCount)
    {
        public static UserAdventBattleModel Empty { get; } = new UserAdventBattleModel(
            MasterDataId.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleChallengeCount.Empty,
            AdventBattleChallengeCount.Empty,
            StageClearCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
