using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record InGamePreferenceInitializationResult(
        BattleSpeed BattleSpeed, 
        InGameAutoEnabledFlag IsAutoEnabled,
        InGameContinueSelectingFlag IsContinueSelecting)
    {
        public static InGamePreferenceInitializationResult Empty { get; } = new(
            BattleSpeed.x1, 
            InGameAutoEnabledFlag.False,
            InGameContinueSelectingFlag.False);
    }
}