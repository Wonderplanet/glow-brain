using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record BattleSpeedInitializationResult(
        BattleSpeed CurrentBattleSpeed,
        IReadOnlyList<BattleSpeed> BattleSpeedList)
    {
        public static BattleSpeedInitializationResult Empty { get; } = new(
            BattleSpeed.x1, 
            new List<BattleSpeed>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}