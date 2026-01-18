using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GameModeSelect.Domain;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public record GameModeSelectItemViewModel(
        GameModeSelectingFlag IsSelected,
        GameModeType Type,
        MasterDataId MstEventId,
        DateTimeOffset EndAt,
        GameModeSelectAssetKey GameModeSelectAssetKey,
        EventAssetKey EventAssetKey,
        TimeSpan LimitTime)
    {
        public bool ShowsLimitTime => LimitTime != TimeSpan.Zero;
    };
}
