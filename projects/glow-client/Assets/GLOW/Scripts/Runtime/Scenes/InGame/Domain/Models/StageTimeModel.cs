using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record StageTimeModel(
        InGameTimeRule Rule,
        TickCount CurrentTickCount,
        InGameTimeLimit StageTimeLimit,
        InGameTimeLimit RemainingTime,
        RemainingTimeTextColor RemainingTimeTextColor)
    {
        public static StageTimeModel Empty { get; } = new (
            InGameTimeRule.None,
            TickCount.Empty,
            InGameTimeLimit.Empty,
            InGameTimeLimit.Empty,
            RemainingTimeTextColor.Default);

        public static StageTimeModel Zero { get; } = new (
            InGameTimeRule.None,
            TickCount.Zero,
            InGameTimeLimit.Zero,
            InGameTimeLimit.Zero,
            RemainingTimeTextColor.Default);

        /// <summary> 制限時間のあるステージか </summary>
        public bool HasTimeLimit => StageTimeLimit > InGameTimeLimit.Zero;

        /// <summary> 制限時間を過ぎているか </summary>
        public bool IsTimeLimitOver => HasTimeLimit && RemainingTime <= InGameTimeLimit.Zero;

        /// <summary> 経過時間 </summary>
        public InGameTimeLimit ElapsedTime => StageTimeLimit - RemainingTime;

        /// <summary> カウントダウンを表示するか </summary>
        public bool IsShowCountDown => Rule != InGameTimeRule.SpeedAttack;
    }
}
