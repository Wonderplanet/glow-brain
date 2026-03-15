using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.UseCases
{
    public class GetEncyclopediaSeriesUrlUseCase
    {
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public JumpPlusUrl GetJumpPlusUrl(MasterDataId mstSeriesId)
        {
            var mstSeries = MstSeriesDataRepository.GetMstSeriesModel(mstSeriesId);
            return mstSeries.JumpPlusUrl;
        }
    }
}
