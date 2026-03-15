namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record GeneratedFirstAttackFlag(bool Value)
    {
        public static GeneratedFirstAttackFlag False { get; } = new(false);
        public static GeneratedFirstAttackFlag True { get; } = new(true);
        public static implicit operator bool(GeneratedFirstAttackFlag value) => value.Value;
        public static bool operator true(GeneratedFirstAttackFlag value) => value.Value;
        public static bool operator false(GeneratedFirstAttackFlag value) => !value.Value;
    }
}
