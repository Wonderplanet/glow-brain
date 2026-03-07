using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ArtworkEffectModel(IReadOnlyList<ArtworkEffectElement> EffectElements)
    {
        public static ArtworkEffectModel Empty { get; } = new (new List<ArtworkEffectElement>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
