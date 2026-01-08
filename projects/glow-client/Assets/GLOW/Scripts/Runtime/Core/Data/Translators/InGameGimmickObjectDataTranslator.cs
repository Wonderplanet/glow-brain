using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class InGameGimmickObjectDataTranslator
    {
        public static MstInGameGimmickObjectModel Translate(MstInGameGimmickObjectData data)
        {
            return new MstInGameGimmickObjectModel(
                new MasterDataId(data.Id),
                string.IsNullOrEmpty(data.AssetKey)
                    ? InGameGimmickObjectAssetKey.Empty
                    : new InGameGimmickObjectAssetKey(data.AssetKey));
        }
    }
}
