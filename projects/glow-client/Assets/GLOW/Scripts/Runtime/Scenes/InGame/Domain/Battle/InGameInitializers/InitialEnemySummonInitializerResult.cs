using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record InitialEnemySummonInitializerResult(
        IReadOnlyList<CharacterUnitModel> InitialEnemyUnits)
    {
        public static InitialEnemySummonInitializerResult Empty { get; } = new (
            new List<CharacterUnitModel>());
    }
}
