using System;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserLoginInfoModel(
        DateTimeOffset? LastLoginAt,//初回チュートリアル中などログイン時間が無い場合あり
        LoginDayCount LoginDayCount,
        LoginDayCount LoginContinueDayCount)
    {
        public static UserLoginInfoModel Empty { get; } = new(
            DateTimeOffset.MinValue,
            LoginDayCount.Empty,
            LoginDayCount.Empty);
    }
}
