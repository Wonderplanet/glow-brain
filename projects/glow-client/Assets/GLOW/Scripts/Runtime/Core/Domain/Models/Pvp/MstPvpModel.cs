using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpModel(
        MasterDataId Id,
        PvpRankClassType? MinPvpRankClass,
        PvpDailyChallengeCount MaxDailyChallengeCount,
        PvpDailyChallengeCount MaxDailyItemChallengeCount,
        PvpItemChallengeCost ItemChallengeCost,
        BattlePoint InitialBattlePoint,
        PvpName Name,
        PvpDescription Description)
    {
        public static MstPvpModel Empty { get; } = new MstPvpModel(
            MasterDataId.Empty,
            null,
            PvpDailyChallengeCount.Empty,
            PvpDailyChallengeCount.Empty,
            PvpItemChallengeCost.Empty,
            BattlePoint.Empty,
            PvpName.Empty,
            PvpDescription.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
