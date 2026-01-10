using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class ItemDataTranslator
    {
        public static MstItemModel ToItemModel(MstItemData mstItemData, MstItemI18nData mstItemI18NData)
        {
            var destinationOprProductId = string.IsNullOrEmpty(mstItemData.DestinationOprProductId)
                ? MasterDataId.Empty
                : new MasterDataId(mstItemData.DestinationOprProductId);

            return new MstItemModel(
                new MasterDataId(mstItemData.Id),
                mstItemI18NData != null ? new ItemName(mstItemI18NData.Name) : ItemName.Empty,
                mstItemI18NData != null ? new ItemDescription(mstItemI18NData.Description) : ItemDescription.Empty,
                mstItemData.Type,
                mstItemData.GroupType,
                mstItemData.Rarity,
                string.IsNullOrEmpty(mstItemData.EffectValue)
                    ? ItemEffectValue.Empty
                    : new ItemEffectValue(mstItemData.EffectValue),
                new SortOrder(mstItemData.SortOrder),
                new ItemAssetKey(mstItemData.AssetKey),
                string.IsNullOrEmpty(mstItemData.MstSeriesId)
                    ? MasterDataId.Empty
                    : new MasterDataId(mstItemData.MstSeriesId),
                destinationOprProductId,
                new UnlimitedCalculableDateTimeOffset(mstItemData.StartDate),
                new UnlimitedCalculableDateTimeOffset(mstItemData.EndDate));
        }

        public static UserItemModel ToUserItemModel(UsrItemData data)
        {
            return new UserItemModel(
                !string.IsNullOrEmpty(data.UsrItemId) ? new UserDataId(data.UsrItemId) : UserDataId.Empty,
                !string.IsNullOrEmpty(data.MstItemId) ? new MasterDataId(data.MstItemId) : MasterDataId.Empty,
                new ItemAmount(data.Amount));
        }
    }
}
