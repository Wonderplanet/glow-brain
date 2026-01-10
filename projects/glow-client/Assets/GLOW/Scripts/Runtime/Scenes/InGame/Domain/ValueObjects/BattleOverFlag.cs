namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record BattleOverFlag(bool Value)
    {
        public static BattleOverFlag False { get; } = new(false);
        public static BattleOverFlag True { get; } = new(true);
        
        public static implicit operator bool(BattleOverFlag value) => value.Value;
    }
}