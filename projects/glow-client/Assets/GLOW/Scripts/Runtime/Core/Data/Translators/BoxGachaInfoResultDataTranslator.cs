using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class BoxGachaInfoResultDataTranslator
    {
        public static BoxGachaInfoResultModel Translate(BoxGachaInfoResultData data)
        {
            if (data.UsrBoxGacha == null) return BoxGachaInfoResultModel.Empty;
            
            var userBoxGachaModel = UserBoxGachaDataTranslator.ToUserBoxGachaModel(data.UsrBoxGacha);
            return new BoxGachaInfoResultModel(userBoxGachaModel);
        }
    }
}