namespace GLOW.Core.Domain.ValueObjects
{
    public record IsEncyclopediaSpecialAttackPositionRight(bool Value)
    {
        public static IsEncyclopediaSpecialAttackPositionRight False { get; } = new(false);
        public static IsEncyclopediaSpecialAttackPositionRight True { get; } = new(true);

        public static implicit operator bool(IsEncyclopediaSpecialAttackPositionRight value) => value.Value;
    }
}
