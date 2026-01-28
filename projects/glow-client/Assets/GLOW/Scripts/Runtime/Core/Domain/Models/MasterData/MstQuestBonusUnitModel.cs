using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstQuestBonusUnitModel(
        MasterDataId Id,
        MasterDataId MstQuestId,
        MasterDataId MstUnitId,
        BonusRate CoinBonusRate,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt
        )
    {
        public static MstQuestBonusUnitModel Empty { get; } = new MstQuestBonusUnitModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            BonusRate.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue
            );
    }
}
