using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaEnemyDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Domain.UseCases
{
    public class GetEncyclopediaEnemyDetailUseCase
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public EncyclopediaEnemyDetailModel GetEnemyDetail(MasterDataId mstEnemyCharacterId)
        {
            var mstEnemyCharacter = MstEnemyCharacterDataRepository.GetEnemyCharacter(mstEnemyCharacterId);
            var mstSeries = MstSeriesDataRepository.GetMstSeriesModel(mstEnemyCharacter.MstSeriesId);
            var seriesLogoImagePath = new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstSeries.SeriesAssetKey.Value));

            return new EncyclopediaEnemyDetailModel(
                mstEnemyCharacter.Name,
                seriesLogoImagePath,
                mstEnemyCharacter.Description);
        }
    }
}
