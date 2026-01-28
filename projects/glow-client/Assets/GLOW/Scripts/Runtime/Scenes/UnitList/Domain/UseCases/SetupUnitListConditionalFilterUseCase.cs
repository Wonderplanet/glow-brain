using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitList.Domain.UseCases
{
    public class SetupUnitListConditionalFilterUseCase
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }

        public void Setup()
        {
            var model = UnitSortFilterCacheRepository.GetModel(UnitSortFilterCacheType.UnitList);
            UnitSortFilterCacheRepository.UpdateBonusFilter(
                UnitSortFilterCacheType.UnitList,
                model.FilterBonusModel with
                {
                    EnableBonus = FilterBonusFlag.False
                });
            UnitSortFilterCacheRepository.UpdateFormationFilter(
                UnitSortFilterCacheType.UnitList,
                model.FilterFormationModel with
                {
                    EnableFormationFlag = FilterFormationFlag.False
                });
        }
    }
}
