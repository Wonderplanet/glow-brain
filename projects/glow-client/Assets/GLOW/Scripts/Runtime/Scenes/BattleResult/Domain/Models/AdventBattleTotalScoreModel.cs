using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record AdventBattleTotalScoreModel(
        AdventBattleScore BeforeAdventBattleScore,
        AdventBattleScore AdventBattleScore)
    {
        public static AdventBattleTotalScoreModel Empty { get; } = new AdventBattleTotalScoreModel(
            AdventBattleScore.Empty,
            AdventBattleScore.Empty
        );
    }
}