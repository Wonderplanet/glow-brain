using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Core.Domain.Models
{
    public record MstQuestModel(
        MasterDataId Id,
        QuestName Name,
        QuestName CategoryName,
        QuestFlavorText QuestFlavorText,
        QuestAssetKey AssetKey,
        QuestType QuestType,
        MasterDataId MstEventId,
        UnlimitedCalculableDateTimeOffset StartDate,
        UnlimitedCalculableDateTimeOffset EndDate,
        SortOrder SortOrder,
        MasterDataId GroupId,
        Difficulty Difficulty
    )
    {
        public static MstQuestModel Empty { get; } = new MstQuestModel(
            MasterDataId.Empty,
            QuestName.Empty,
            QuestName.Empty,
            QuestFlavorText.Empty,
            QuestAssetKey.Empty,
            QuestType.Normal,
            MasterDataId.Empty,
            new UnlimitedCalculableDateTimeOffset(UnlimitedCalculableDateTimeOffset.UnlimitedStartAt),
            new UnlimitedCalculableDateTimeOffset(UnlimitedCalculableDateTimeOffset.UnlimitedEndAt),
            SortOrder.Zero,
            MasterDataId.Empty,
            Difficulty.Normal
        );
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

    };
}
