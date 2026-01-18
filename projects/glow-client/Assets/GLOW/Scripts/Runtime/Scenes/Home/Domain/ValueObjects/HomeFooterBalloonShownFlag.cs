namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeFooterBalloonShownFlag(bool Value)
    {
        public static HomeFooterBalloonShownFlag Empty { get; } = new(false);
        public static HomeFooterBalloonShownFlag True  { get; } = new(true);
        public static HomeFooterBalloonShownFlag False { get; } = new(false);
        
        public static implicit operator bool(HomeFooterBalloonShownFlag flag) => flag.Value;
    };
}