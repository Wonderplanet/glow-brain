using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public record KomaEffectUpdateContext(
        IReadOnlyList<CharacterUnitModel> Units,
        TickCount TickCount)
    {
        public static KomaEffectUpdateContext Empty { get; } = new(
            new List<CharacterUnitModel>(),
            TickCount.Empty);
    }
}
