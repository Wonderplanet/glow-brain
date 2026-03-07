using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainKomaSettingFilterUseCase
    {
        [Inject] IHomeMainKomaSettingFilterCacheRepository HomeMainKomaSettingFilterCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public HomeMainKomaSettingFilterUseCaseModel GetUseCaseModel()
        {
            return new HomeMainKomaSettingFilterUseCaseModel(
                HomeMainKomaSettingFilterCacheRepository.CachedFilterMstSeriesIds,
                CreateSeriesFilterTitleModels()
            );
        }

        IReadOnlyList<SeriesFilterTitleModel> CreateSeriesFilterTitleModels()
        {
            return MstSeriesDataRepository.GetMstSeriesModels()
                .Select(m =>
                {
                    return new SeriesFilterTitleModel(
                        m.Id,
                        new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(m.SeriesAssetKey.Value)),
                        m.PrefixWord
                    );
                })
                .ToList();
        }
    }
}
