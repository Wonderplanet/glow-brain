using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstStageEventSettingModel(
        MasterDataId Id,
        MasterDataId MstStageId,
        ResetType? ResetType,
        ClearableCount ClearableCount,
        AdChallengeCount AdChallengeCount,
        KomaBackgroundAssetKey EventTopBackGroundAssetKey,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt
    )
    {
        public static MstStageEventSettingModel Empty { get; }= new(
            Id: MasterDataId.Empty,
            MstStageId: MasterDataId.Empty,
            ResetType: null,
            ClearableCount: ClearableCount.Empty,
            AdChallengeCount: new AdChallengeCount(0),
            EventTopBackGroundAssetKey: KomaBackgroundAssetKey.Empty,
            StartAt: DateTimeOffset.MinValue,
            EndAt: DateTimeOffset.MaxValue
        );
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
