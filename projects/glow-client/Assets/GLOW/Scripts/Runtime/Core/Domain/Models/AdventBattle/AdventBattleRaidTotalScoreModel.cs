using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleRaidTotalScoreModel(
        MasterDataId MstAdventBattleId,
        AdventBattleRaidTotalScore AdventBattleRaidTotalScore)
    {
        public static AdventBattleRaidTotalScoreModel Empty { get; } = new(
            MasterDataId.Empty, 
            AdventBattleRaidTotalScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}