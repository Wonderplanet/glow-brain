using GLOW.Scenes.BattleResult.Domain.Enum;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record CheckContentOpenModel(
        InGameStageType InGameStageType,
        InGameStageValidFlag IsInGameStageValid)
    {
        public static CheckContentOpenModel Empty { get; } = new CheckContentOpenModel(
            InGameStageType.NormalStage,
            InGameStageValidFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}