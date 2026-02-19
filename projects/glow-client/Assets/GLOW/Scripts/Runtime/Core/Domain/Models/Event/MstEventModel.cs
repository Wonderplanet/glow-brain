using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstEventModel(
        MasterDataId Id,
        MasterDataId MstSeriesId,
        bool IsDisplayedSeriesLogo,
        bool IsDisplayedJumpPlus,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt,
        EventAssetKey AssetKey,
        EventName Name,
        string BallonText
    )
    {
        public static MstEventModel Empty { get; } = new MstEventModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            false,
            false,
            DateTimeOffset.MinValue,
            DateTimeOffset.MaxValue,
            EventAssetKey.Empty,
            EventName.Empty,
            string.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
