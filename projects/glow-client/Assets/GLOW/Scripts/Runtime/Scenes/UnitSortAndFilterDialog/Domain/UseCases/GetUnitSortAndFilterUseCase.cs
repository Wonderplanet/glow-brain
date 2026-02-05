using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases
{
    public class GetUnitSortAndFilterUseCase
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstAbilityDescriptionDataRepository MstAbilityDescriptionDataRepository { get; }

        public UnitSortAndFilterDialogUseCaseModel GetUnitSortAndFilterDialogModel(UnitSortFilterCacheType cacheType)
        {
            var unitSortFilterCategoryModel = UnitSortFilterCacheRepository.GetModel(cacheType);
            var mstSeriesModels = MstSeriesDataRepository.GetMstSeriesModels();
            var mstAbilityDescriptionModels = MstAbilityDescriptionDataRepository.GetAbilityDescriptionModels();

            return new UnitSortAndFilterDialogUseCaseModel(
                cacheType,
                unitSortFilterCategoryModel,
                mstSeriesModels,
                mstAbilityDescriptionModels);
        }
    }
}
