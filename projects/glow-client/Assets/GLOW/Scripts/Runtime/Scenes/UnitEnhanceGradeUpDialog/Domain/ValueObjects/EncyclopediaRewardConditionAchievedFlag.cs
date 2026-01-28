namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.ValueObjects
{
    public record EncyclopediaRewardConditionAchievedFlag(bool Value)
    {
        public static EncyclopediaRewardConditionAchievedFlag Empty { get; } = new EncyclopediaRewardConditionAchievedFlag(false);

        public bool IsEmpty => ReferenceEquals(this, Empty);

        public static implicit operator bool(EncyclopediaRewardConditionAchievedFlag conditionAchievedFlag) => conditionAchievedFlag.Value;
    }
}
