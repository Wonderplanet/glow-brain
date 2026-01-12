using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionEventDailyBonusScheduleModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt)
    {
        public static MstMissionEventDailyBonusScheduleModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
