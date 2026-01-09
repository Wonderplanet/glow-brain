using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record UserStageEventModel(
        MasterDataId MstStageId,
        StageClearCount TotalClearCount,
        StageClearCount ResetClearCount, // 日跨ぎするとカウントリセットされる(日跨ぎリセット無いときはTotalClearCountと同じ値)
        StageClearCount ResetAdChallengeCount,// 日跨ぎするとカウントリセットされる
        DateTimeOffset? LatestResetAt,
        DateTimeOffset? LastChallengedAt,
        EventClearTimeMs ClearTimeMs,
        EventClearTimeMs ResetClearTimeMs,
        DateTimeOffset? LatestEventSettingEndAt):IStageClearCountable
    {
        public static UserStageEventModel Empty { get; } =
            new UserStageEventModel(
                MasterDataId.Empty,
                StageClearCount.Empty,
                StageClearCount.Empty,
                StageClearCount.Empty,
                null,
                null,
                EventClearTimeMs.Empty,
                EventClearTimeMs.Empty,
                null);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public StageClearCount ClearCount => ResetClearCount;

    }


}
