using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstQuestEventBonusScheduleModel(
        MasterDataId MstQuestId,
        EventBonusGroupId EventBonusGroupId,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt
    )
    {
        public static MstQuestEventBonusScheduleModel Empty { get; } = new(
            MasterDataId.Empty,
            EventBonusGroupId.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
