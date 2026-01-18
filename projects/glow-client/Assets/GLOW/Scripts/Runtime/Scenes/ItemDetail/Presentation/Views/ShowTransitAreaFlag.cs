namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    public record ShowTransitAreaFlag(bool Value)
    {
        public static ShowTransitAreaFlag True { get; } = new(true);
        public static ShowTransitAreaFlag False { get; } = new(false);
        public static implicit operator bool(ShowTransitAreaFlag transitionButtonGrayOutFlag) => transitionButtonGrayOutFlag.Value;
    };
}
