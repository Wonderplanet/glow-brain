namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageRewardCompleteFlag(bool Value)
    {
        public static StageRewardCompleteFlag Empty { get; } = new(false);
        public static StageRewardCompleteFlag True { get; } = new(true);
        public static StageRewardCompleteFlag False { get; } = new(false);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static implicit operator bool(StageRewardCompleteFlag stageRewardCompleteFlag)
        {
            return stageRewardCompleteFlag.Value;
        }
    }
}
