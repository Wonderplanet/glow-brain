using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record ArtworkEffectDescriptionModel(
        ArtworkGradeLevel GradeLevel,
        ArtworkEffectDescription Description)
    {
        public static ArtworkEffectDescriptionModel Empty { get; } = new(
            ArtworkGradeLevel.Empty,
            ArtworkEffectDescription.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
