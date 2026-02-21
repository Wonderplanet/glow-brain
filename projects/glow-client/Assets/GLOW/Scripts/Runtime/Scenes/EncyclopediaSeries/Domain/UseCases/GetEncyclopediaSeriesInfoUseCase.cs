using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.UseCases
{
    public class GetEncyclopediaSeriesInfoUseCase
    {
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public (SeriesLogoImagePath logo, SeriesIconImagePath icon) GetSeriesInfo(MasterDataId mstSeriesId)
        {
            var mstSeries = MstSeriesDataRepository.GetMstSeriesModel(mstSeriesId);
            var logo = new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstSeries.SeriesAssetKey.Value));
            var icon = new SeriesIconImagePath(SeriesAssetPath.GetSeriesBannerPath(mstSeries.SeriesAssetKey.Value));
            return (logo, icon);
        }
    }
}
