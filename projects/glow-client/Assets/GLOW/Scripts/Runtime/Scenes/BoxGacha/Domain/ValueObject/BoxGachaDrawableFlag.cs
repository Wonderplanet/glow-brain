namespace GLOW.Scenes.BoxGacha.Domain.ValueObject
{
    public record BoxGachaDrawableFlag(bool Value)
    {
        public static BoxGachaDrawableFlag True { get; } = new BoxGachaDrawableFlag(true);
        public static BoxGachaDrawableFlag False { get; } = new BoxGachaDrawableFlag(false);
        
        public static implicit operator bool(BoxGachaDrawableFlag flag) => flag.Value;
    }
}