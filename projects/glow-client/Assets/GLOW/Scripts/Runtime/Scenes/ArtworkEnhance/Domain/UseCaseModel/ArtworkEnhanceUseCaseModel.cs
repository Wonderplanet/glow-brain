using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkEnhanceUseCaseModel(
        MasterDataId MstArtworkId,
        ArtworkName Name,
        Rarity Rarity,
        ArtworkGradeLevel GradeLevel,
        SeriesAssetKey SeriesLogoImageKey,
        ArtworkCompletedFlag ArtworkCompletedFlag,
        ArtworkGradeUpAvailableFlag GradeUpAvailableFlag,
        ArtworkGradeMaxLimitFlag GradeMaxLimitFlag,
        ArtworkAcquisitionRouteExistsFlag AcquisitionRouteExistsFlag,
        ArtworkEffectDescription EffectDescription,
        ArtworkDescription ArtworkDescription,
        IReadOnlyList<PlayerResourceModel> GradeUpIconModels,
        IReadOnlyList<ArtworkGradeUpItemEnoughFlag> GradeUpItemEnoughFlags)
    {
        public static ArtworkEnhanceUseCaseModel Empty { get; } =
            new ArtworkEnhanceUseCaseModel(
                MasterDataId.Empty,
                ArtworkName.Empty,
                Rarity.UR,
                ArtworkGradeLevel.Empty,
                SeriesAssetKey.Empty,
                ArtworkCompletedFlag.False,
                ArtworkGradeUpAvailableFlag.False,
                ArtworkGradeMaxLimitFlag.False,
                ArtworkAcquisitionRouteExistsFlag.False,
                ArtworkEffectDescription.Empty,
                ArtworkDescription.Empty,
                new List<PlayerResourceModel>(),
                new List<ArtworkGradeUpItemEnoughFlag>());

        public static ArtworkEnhanceUseCaseModel CreateDefault()
        {
            return new ArtworkEnhanceUseCaseModel(
                MasterDataId.Empty,
                ArtworkName.Empty,
                Rarity.UR,
                ArtworkGradeLevel.Empty,
                SeriesAssetKey.Empty,
                ArtworkCompletedFlag.False,
                ArtworkGradeUpAvailableFlag.False,
                ArtworkGradeMaxLimitFlag.False,
                ArtworkAcquisitionRouteExistsFlag.False,
                ArtworkEffectDescription.Empty,
                ArtworkDescription.Empty,
                new List<PlayerResourceModel>(),
                new List<ArtworkGradeUpItemEnoughFlag>());
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
