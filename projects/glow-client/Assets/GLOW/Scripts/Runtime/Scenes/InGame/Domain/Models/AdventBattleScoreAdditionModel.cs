using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AdventBattleScoreAdditionModel(
        ScoreAdditionType Type,
        DamageScoreAdditionalCoef DamageScoreAdditionalCoef)
    {
        public static AdventBattleScoreAdditionModel Empty { get; } = new(
            ScoreAdditionType.AllEnemiesAndOutPost,
            DamageScoreAdditionalCoef.Empty);
    };
}
