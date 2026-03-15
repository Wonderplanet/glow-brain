using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class UpdateHomeMainKomaSettingFilterUseCase
    {
        [Inject] IHomeMainKomaSettingFilterCacheRepository HomeMainKomaSettingFilterCacheRepository { get; }

        public void UpdateCacheRepository(IReadOnlyList<MasterDataId> filterdMstSeriesIds)
        {
            HomeMainKomaSettingFilterCacheRepository.UpdateFilterMstSeriesIds(filterdMstSeriesIds);
        }
    }
}