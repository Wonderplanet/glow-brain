using System;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Translators
{
    public static class ItemModelTranslator
    {
        public static ItemModel ToItemModel(UserItemModel userItemModel, MstItemModel mstItemModel)
        {
            return new ItemModel(
                userItemModel.MstItemId,
                mstItemModel.Name,
                mstItemModel.Description,
                mstItemModel.Type,
                mstItemModel.GroupType,
                mstItemModel.Rarity,
                mstItemModel.SortOrder,
                mstItemModel.ItemAssetKey,
                userItemModel.Amount,
                mstItemModel.MstSeriesId,
                mstItemModel.StartAt,
                mstItemModel.EndAt);
        }

        public static ItemModel ToItemModel(MstItemModel mstItemModel)
        {
            return new ItemModel(
                mstItemModel.Id,
                mstItemModel.Name,
                mstItemModel.Description,
                mstItemModel.Type,
                mstItemModel.GroupType,
                mstItemModel.Rarity,
                mstItemModel.SortOrder,
                mstItemModel.ItemAssetKey,
                ItemAmount.Empty,
                mstItemModel.MstSeriesId,
                mstItemModel.StartAt,
                mstItemModel.EndAt);
        }

        public static ItemModel ToItemModel(MstItemModel mstItemModel, ItemAmount amount)
        {
            return new ItemModel(
                mstItemModel.Id,
                mstItemModel.Name,
                mstItemModel.Description,
                mstItemModel.Type,
                mstItemModel.GroupType,
                mstItemModel.Rarity,
                mstItemModel.SortOrder,
                mstItemModel.ItemAssetKey,
                amount,
                mstItemModel.MstSeriesId,
                mstItemModel.StartAt,
                mstItemModel.EndAt);
        }
    }
}
