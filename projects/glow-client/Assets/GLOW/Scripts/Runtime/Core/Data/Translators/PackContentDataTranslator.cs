using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Translators
{
    public static class PackContentDataTranslator
    {
        public static MstPackContentModel ToPackContentModel(MstPackContentData mstPackContentData)
        {
            var resourceId = string.IsNullOrEmpty(mstPackContentData.ResourceId)
                ? MasterDataId.Empty
                : new MasterDataId(mstPackContentData.ResourceId);

            return new MstPackContentModel(
                new MasterDataId(mstPackContentData.MstPackId),
                mstPackContentData.ResourceType,
                resourceId,
                new ObscuredPlayerResourceAmount(mstPackContentData.ResourceAmount),
                new BonusFlag(mstPackContentData.IsBonus),
                new SortOrder(mstPackContentData.DisplayOrder)
            );
        }
    }
}
