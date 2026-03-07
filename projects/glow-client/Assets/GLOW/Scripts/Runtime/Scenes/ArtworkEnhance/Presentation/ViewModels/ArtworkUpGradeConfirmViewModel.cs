using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkUpGradeConfirmViewModel(
        ArtworkName ArtworkName,
        IReadOnlyList<RequiredEnhanceItemViewModel> RequiredEnhanceItemViewModels,
        ArtworkGradeLevel CurrentGradeLevel,
        ArtworkGradeLevel NextGradeLevel,
        ArtworkEffectDescription EffectDescription,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit)
    {
        public static ArtworkUpGradeConfirmViewModel Empty { get; } =
            new ArtworkUpGradeConfirmViewModel(
                ArtworkName.Empty,
                new List<RequiredEnhanceItemViewModel>(),
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
