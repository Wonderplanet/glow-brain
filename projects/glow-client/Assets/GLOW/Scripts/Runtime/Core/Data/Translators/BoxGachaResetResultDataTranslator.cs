using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class BoxGachaResetResultDataTranslator
    {
        public static BoxGachaResetResultModel Translate(BoxGachaResetResultData data)
        {
            var userBoxGachaModel = UserBoxGachaDataTranslator.ToUserBoxGachaModel(data.UsrBoxGacha);
            return new BoxGachaResetResultModel(userBoxGachaModel);
        }
    }
}