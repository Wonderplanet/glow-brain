using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaUnitDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Domain.UseCases
{
    public class GetEncyclopediaUnitDetailUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public EncyclopediaUnitDetailModel GetUnitDetail(MasterDataId mstUnitId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            var mstSeries = MstSeriesDataRepository.GetMstSeriesModel(mstUnit.MstSeriesId);
            var seriesLogoImagePathString = SeriesAssetPath.GetSeriesLogoPath(mstSeries.SeriesAssetKey.Value);
            var seriesLogoImagePath = new SeriesLogoImagePath(seriesLogoImagePathString);
            var specialAttack = mstUnit.GetSpecialAttack(UnitGrade.Minimum);

            return new EncyclopediaUnitDetailModel(
                mstUnit.RoleType,
                mstUnit.Rarity,
                mstUnit.Name,
                mstUnit.AssetKey,
                seriesLogoImagePath,
                mstUnit.Description,
                specialAttack.Name
            );
        }
    }
}
