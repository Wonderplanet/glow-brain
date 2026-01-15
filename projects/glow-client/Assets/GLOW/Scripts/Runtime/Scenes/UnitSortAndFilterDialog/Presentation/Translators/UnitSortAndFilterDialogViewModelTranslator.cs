using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Translators
{
    public class UnitSortAndFilterDialogViewModelTranslator
    {
        public UnitSortAndFilterDialogViewModel ToTranslate(
            UnitSortAndFilterDialogUseCaseModel useCaseModel,
            MasterDataId specialRuleTargetMstStageId,
            InGameContentType specialRuleContentType)
        {
            var seriesFilterTitleModels = useCaseModel.MstSeriesModels
                .Select(model =>
                {
                    var seriesLogoImagePath =
                        new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(model.SeriesAssetKey.Value));
                    return new SeriesFilterTitleModel(model.Id, seriesLogoImagePath, model.PrefixWord);

                }).ToList();

            var abilityFilterTitleModels = useCaseModel.MstAbilityDescriptionModels.Select(model =>
                new UnitAbilityFilterTitleModel(model.UnitAbilityType, model.FilterTitle)).ToList();

            return new UnitSortAndFilterDialogViewModel(
                useCaseModel.UnitSortFilterCacheType,
                useCaseModel.CategoryModel,
                seriesFilterTitleModels,
                abilityFilterTitleModels,
                specialRuleTargetMstStageId,
                specialRuleContentType);
        }
    }
}
