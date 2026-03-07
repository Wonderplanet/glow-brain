using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkGradeUpAnimViewModel(
        ArtworkName ArtworkName,
        ArtworkGradeLevel BeforeGradeLevel,
        ArtworkGradeLevel AfterGradeLevel,
        ArtworkEffectDescription EffectDescription,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit);
}
