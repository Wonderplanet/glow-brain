using GLOW.Core.Domain.Models.AdventBattle;

namespace GLOW.Core.Domain.Repositories
{
    public interface IAdventBattlePreferenceRepository
    {
        AdventBattleRaidTotalScoreModel EvaluatedRaidTotalScoreModelForRewards { get; }
        void SetEvaluatedRaidTotalScoreModelForRewards(AdventBattleRaidTotalScoreModel model);
    }
}