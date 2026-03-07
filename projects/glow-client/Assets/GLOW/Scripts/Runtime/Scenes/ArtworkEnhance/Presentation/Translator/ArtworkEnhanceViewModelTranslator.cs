using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Translator
{
    public class ArtworkEnhanceViewModelTranslator
    {
        public static ArtworkEnhanceViewModel Translate(ArtworkEnhanceUseCaseModel useCaseModel)
        {
            var iconViewModels =
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(useCaseModel.GradeUpIconModels);

            // 必要素材アイコンViewModelリストを作成
            var requiredIconViewModels = CreateRequiredIconViewModels(iconViewModels, useCaseModel);


            return new ArtworkEnhanceViewModel(
                useCaseModel.Name,
                useCaseModel.Rarity,
                useCaseModel.GradeLevel,
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(useCaseModel.SeriesLogoImageKey.Value)),
                useCaseModel.ArtworkCompletedFlag,
                useCaseModel.GradeUpAvailableFlag,
                useCaseModel.GradeMaxLimitFlag,
                useCaseModel.AcquisitionRouteExistsFlag,
                useCaseModel.EffectDescription,
                useCaseModel.ArtworkDescription,
                iconViewModels,
                requiredIconViewModels);
        }

        static IReadOnlyList<ArtworkGradeUpRequiredIconViewModel> CreateRequiredIconViewModels(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            ArtworkEnhanceUseCaseModel useCaseModel)
        {
            return iconViewModels
                .Select(
                    (iconViewModel, index) => new ArtworkGradeUpRequiredIconViewModel(
                        ItemIconAssetPath.FromAssetKey(useCaseModel.GradeUpIconModels[index].AssetKey),
                        iconViewModel.Rarity,
                        new ItemAmount(iconViewModel.Amount.Value),
                        useCaseModel.GradeUpItemEnoughFlags[index]))
                .ToList();
        }
    }
}
