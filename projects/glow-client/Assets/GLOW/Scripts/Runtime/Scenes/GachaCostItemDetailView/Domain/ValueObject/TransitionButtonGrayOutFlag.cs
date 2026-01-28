namespace GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject
{
    public record TransitionButtonGrayOutFlag(bool Value)
    {
        public static TransitionButtonGrayOutFlag False { get; } = new(false);
        public static TransitionButtonGrayOutFlag True { get; } = new(true);
        public static implicit operator bool(TransitionButtonGrayOutFlag transitionButtonGrayOutFlag) => transitionButtonGrayOutFlag.Value;
    }
}