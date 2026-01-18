using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstArtworkDataTranslator
    {
        public static MstArtworkModel Translate(MstArtworkData mst, MstArtworkI18nData i18n)
        {
            return new MstArtworkModel(
                new MasterDataId(mst.Id),
                new MasterDataId(mst.MstSeriesId),
                new HP(mst.OutpostAdditionalHp),
                new ArtworkAssetKey(mst.AssetKey),
                new SortOrder(mst.SortOrder),
                new ArtworkName(i18n?.Name),
                new ArtworkDescription(i18n?.Description)
                );
        }
    }
}
