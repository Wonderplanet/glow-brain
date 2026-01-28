using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEventTranslator
    {
        public static MstEventModel Translate(MstEventData data, MstEventI18nData i18n)
        {
            return new MstEventModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstSeriesId),
                data.IsDisplayedSeriesLogo,
                data.IsDisplayedJumpPlus,
                data.StartAt,
                data.EndAt,
                new EventAssetKey(data.AssetKey),
                new EventName(i18n.Name),
                i18n.Balloon
            );
        }

    }
}
