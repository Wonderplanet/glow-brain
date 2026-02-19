using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class BoxGachaDrawResultDataTranslator
    {
        /// <summary>
        /// BoxGachaDrawResultDataをBoxGachaDrawResultModelに変換する
        /// </summary>
        public static BoxGachaDrawResultModel Translate(BoxGachaDrawResultData data)
        {
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);

            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var userUnitModels = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userArtworkModels = data.UsrArtworks
                .Select(UserArtworkDataTranslator.ToUserArtworkModel)
                .ToList();

            var userArtworkFragmentModels = data.UsrArtworkFragments
                .Select(UserArtworkFragmentDataTranslator.ToUserArtworkFragmentModel)
                .ToList();

            var userBoxGachaModel = UserBoxGachaDataTranslator.ToUserBoxGachaModel(data.UsrBoxGacha);

            var boxGachaRewardModels = data.BoxGachaRewards
                .Select(BoxGachaRewardDataTranslator.ToBoxGachaRewardModel)
                .ToList();

            return new BoxGachaDrawResultModel(
                userParameterModel,
                userItemModels,
                userUnitModels,
                userArtworkModels,
                userArtworkFragmentModels,
                userBoxGachaModel,
                boxGachaRewardModels);
        }
    }
}