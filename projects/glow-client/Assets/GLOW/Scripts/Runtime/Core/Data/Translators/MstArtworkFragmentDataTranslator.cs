using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstArtworkFragmentDataTranslator
    {
        public static MstArtworkFragmentModel Translate(MstArtworkFragmentData mst, MstArtworkFragmentI18nData i18n,
            MstArtworkFragmentPositionData pos)
        {
            return new MstArtworkFragmentModel(
                new MasterDataId(mst.Id),
                new MasterDataId(mst.MstArtworkId),
                new MasterDataId(mst.DropGroupId),
                new Percentage(mst.DropPercentage),
                mst.Rarity,
                new ArtworkFragmentAssetNum(mst.AssetNum),
                new ArtworkFragmentName(i18n.Name),
                new ArtworkFragmentPositionNum(pos.Position));
        }
    }
}
