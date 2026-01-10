namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record BattleEffectSignal(string Value)
    {
        public static BattleEffectSignal Empty { get; } = new(string.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
