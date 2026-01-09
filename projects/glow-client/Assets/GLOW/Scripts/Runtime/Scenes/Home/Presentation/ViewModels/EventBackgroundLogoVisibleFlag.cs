namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record EventBackgroundLogoVisibleFlag(bool Value)
    {
        public static EventBackgroundLogoVisibleFlag True => new EventBackgroundLogoVisibleFlag(true);
        public static EventBackgroundLogoVisibleFlag False => new EventBackgroundLogoVisibleFlag(false);

        public static implicit operator bool(EventBackgroundLogoVisibleFlag flag) => flag.Value;
    }
}
