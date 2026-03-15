namespace GLOW.Scenes.GachaContent.Domain.Model
{
    public record GachaContentDetailButtonFlag(bool Value)
    {
        public static GachaContentDetailButtonFlag True { get; } = new GachaContentDetailButtonFlag(true);
        public static GachaContentDetailButtonFlag False { get; } = new GachaContentDetailButtonFlag(false);
        
        public static implicit operator bool(GachaContentDetailButtonFlag flag) => flag.Value;
    };
}