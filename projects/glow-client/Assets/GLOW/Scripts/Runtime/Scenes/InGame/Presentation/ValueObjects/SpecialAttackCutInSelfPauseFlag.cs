namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record SpecialAttackCutInSelfPauseFlag(bool Value)
    {
        public static SpecialAttackCutInSelfPauseFlag True { get; } = new(true);
        public static SpecialAttackCutInSelfPauseFlag False { get; } = new(false);

        public static implicit operator bool(SpecialAttackCutInSelfPauseFlag flag) => flag.Value;
    }
}
