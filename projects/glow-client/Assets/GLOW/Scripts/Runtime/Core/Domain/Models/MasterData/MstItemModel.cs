using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstItemModel(
        MasterDataId Id,
        ItemName Name,
        ItemDescription Description,
        ItemType Type,
        ItemGroupType GroupType,
        Rarity Rarity,
        ItemEffectValue EffectValue,
        SortOrder SortOrder,
        ItemAssetKey ItemAssetKey,
        MasterDataId MstSeriesId,
        MasterDataId DestinationOprProductId,
        UnlimitedCalculableDateTimeOffset StartAt,
        UnlimitedCalculableDateTimeOffset EndAt
        )
    {
        public static MstItemModel Empty { get; } = new(
            MasterDataId.Empty,
            ItemName.Empty,
            ItemDescription.Empty,
            ItemType.Etc,
            ItemGroupType.Etc,
            Rarity.R,
            ItemEffectValue.Empty,
            SortOrder.MaxValue,
            ItemAssetKey.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            UnlimitedCalculableDateTimeOffset.UnlimitedStart,
            UnlimitedCalculableDateTimeOffset.UnlimitedEnd
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
