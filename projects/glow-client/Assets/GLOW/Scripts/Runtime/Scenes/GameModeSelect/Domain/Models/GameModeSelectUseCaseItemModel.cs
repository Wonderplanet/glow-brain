using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public record GameModeSelectUseCaseItemModel(
        GameModeSelectingFlag IsSelected,
        GameModeType Type,
        MasterDataId MstEventId,
        DateTimeOffset MstEventEndAt,
        GameModeSelectAssetKey AssetKey,
        EventAssetKey EventAssetKey,
        TimeSpan LimitTime)
    {
        public static GameModeSelectUseCaseItemModel Empty { get; } = new GameModeSelectUseCaseItemModel(
            GameModeSelectingFlag.False,
            GameModeType.MeinQuest,
            MasterDataId.Empty,
            DateTimeOffset.MinValue,
            GameModeSelectAssetKey.Empty,
            EventAssetKey.Empty,
            TimeSpan.Zero);

        public static GameModeSelectUseCaseItemModel MainQuest { get; } = new GameModeSelectUseCaseItemModel(
            GameModeSelectingFlag.False,
            GameModeType.MeinQuest,
            MasterDataId.Empty,
            DateTimeOffset.MinValue,
            GameModeSelectAssetKey.Empty,
            EventAssetKey.Empty,
            TimeSpan.Zero);
    };
}
