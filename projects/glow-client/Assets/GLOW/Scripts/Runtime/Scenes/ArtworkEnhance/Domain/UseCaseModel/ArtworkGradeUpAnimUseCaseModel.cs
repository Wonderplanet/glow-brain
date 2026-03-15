using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkGradeUpAnimUseCaseModel(
        ArtworkName ArtworkName,
        ArtworkGradeLevel BeforeGradeLevel,
        ArtworkGradeLevel AfterGradeLevel,
        ArtworkEffectDescription EffectDescription,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit);
}
