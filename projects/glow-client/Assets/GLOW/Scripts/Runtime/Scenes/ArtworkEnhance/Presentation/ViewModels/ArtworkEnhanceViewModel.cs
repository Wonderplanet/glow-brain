using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkEnhanceViewModel(
        ArtworkName Name,
        Rarity Rarity,
        ArtworkGradeLevel GradeLevel,
        SeriesLogoImagePath SeriesLogoImagePath,
        ArtworkCompletedFlag IsArtworkCompleted,
        ArtworkGradeUpAvailableFlag IsGradeUpAvailable,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit,
        ArtworkAcquisitionRouteExistsFlag IsAcquisitionRouteExists,
        ArtworkEffectDescription EffectDescription,
        ArtworkDescription ArtworkDescription,
        IReadOnlyList<PlayerResourceIconViewModel> GradeUpIconViewModels,
        IReadOnlyList<ArtworkGradeUpRequiredIconViewModel> GradeUpRequiredIconViewModels)
    {
        public static ArtworkEnhanceViewModel Empty { get; } =
            new ArtworkEnhanceViewModel(
                new ArtworkName(""),
                Rarity.R,
                ArtworkGradeLevel.Empty,
                SeriesLogoImagePath.Empty,
                ArtworkCompletedFlag.False,
                ArtworkGradeUpAvailableFlag.False,
                ArtworkGradeMaxLimitFlag.False,
                ArtworkAcquisitionRouteExistsFlag.False,
                new ArtworkEffectDescription(""),
                new ArtworkDescription(""),
                new List<PlayerResourceIconViewModel>(),
                new List<ArtworkGradeUpRequiredIconViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
