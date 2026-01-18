namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record BattleGiveUpFlag(bool Value)
    {
        public static BattleGiveUpFlag True { get; } = new(true);
        public static BattleGiveUpFlag False { get; } = new(false);

        public static implicit operator bool(BattleGiveUpFlag flag) => flag.Value;
    }
}
