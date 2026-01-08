using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record ItemModel(
        MasterDataId Id,
        ItemName Name,
        ItemDescription Description,
        ItemType Type,
        ItemGroupType GroupType,
        Rarity Rarity,
        SortOrder SortOrder,
        ItemAssetKey ItemAssetKey,
        ItemAmount Amount,
        MasterDataId MstSeriesId,
        UnlimitedCalculableDateTimeOffset StartAt,
        UnlimitedCalculableDateTimeOffset EndAt) : ILimitedAmountValueObject
    {
        // ItemType.CharacterFragmentは所持上限無いことに注意
        public int HasAmount => Amount.Value;

        public static ItemModel Empty { get; } = new ItemModel(
            MasterDataId.Empty,
            ItemName.Empty,
            ItemDescription.Empty,
            ItemType.Etc,
            ItemGroupType.Etc,
            Rarity.R,
            SortOrder.MaxValue,
            ItemAssetKey.Empty,
            ItemAmount.Empty,
            MasterDataId.Empty,
            UnlimitedCalculableDateTimeOffset.UnlimitedStart,
            UnlimitedCalculableDateTimeOffset.UnlimitedEnd);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsEnhanceItemType()
        {
            return Type is ItemType.RankUpMaterial or ItemType.RankUpMemoryFragment;
        }

        public bool IsCharacterFragmentType()
        {
            return Type == ItemType.CharacterFragment;
        }

        public bool IsFragmentBoxType()
        {
            return Type is ItemType.RandomFragmentBox or ItemType.SelectionFragmentBox or ItemType.SeriesFragmentBox;
        }

        public bool IsItemType()
        {
            return !IsEnhanceItemType() && !IsCharacterFragmentType() && !IsFragmentBoxType();
        }
    }
}
