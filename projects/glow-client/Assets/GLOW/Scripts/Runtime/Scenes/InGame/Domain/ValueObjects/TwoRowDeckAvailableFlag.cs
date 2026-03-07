namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record TwoRowDeckAvailableFlag(bool Value)
    {
        public static TwoRowDeckAvailableFlag True { get; } = new(true);
        public static TwoRowDeckAvailableFlag False { get; } = new(false);

        public static implicit operator bool(TwoRowDeckAvailableFlag flag) => flag.Value;
    }
}