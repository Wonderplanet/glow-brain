using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkUpGradeConfirmUseCaseModel(
        ArtworkName ArtworkName,
        IReadOnlyList<RequiredEnhanceItemUseCaseModel> RequiredEnhanceItemModels,
        ArtworkGradeLevel CurrentGradeLevel,
        ArtworkGradeLevel NextGradeLevel,
        ArtworkEffectDescription EffectDescription,
        ArtworkGradeMaxLimitFlag GradeMaxLimitFlag)
    {
        public static ArtworkUpGradeConfirmUseCaseModel Empty { get; } =
            new ArtworkUpGradeConfirmUseCaseModel(
                ArtworkName.Empty,
                new List<RequiredEnhanceItemUseCaseModel>(),
                ArtworkGradeLevel.Empty,
                ArtworkGradeLevel.Empty,
                ArtworkEffectDescription.Empty,
                ArtworkGradeMaxLimitFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
