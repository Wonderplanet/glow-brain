using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstInGameSpecialRuleModel(
        MasterDataId Id,
        InGameContentType ContentType,
        MasterDataId TargetId,
        RuleType RuleType,
        EventRuleValue RuleValue,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt
    )
    {
        public static MstInGameSpecialRuleModel Empty { get; }= new(
            Id: MasterDataId.Empty,
            ContentType: InGameContentType.Stage,
            TargetId: MasterDataId.Empty,
            RuleType: RuleType.PartyUnitNum,
            RuleValue: EventRuleValue.Empty,
            StartAt: DateTimeOffset.MinValue,
            EndAt: DateTimeOffset.MinValue
        );
        public bool IsEmpty() => ReferenceEquals(this, Empty);

    };

}
