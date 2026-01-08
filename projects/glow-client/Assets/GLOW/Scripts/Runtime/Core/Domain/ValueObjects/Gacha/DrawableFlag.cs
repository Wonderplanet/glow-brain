namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record DrawableFlag(bool Value)
    {
        public static DrawableFlag False = new DrawableFlag(false);
        public static DrawableFlag True = new DrawableFlag(true);
        public static implicit operator bool(DrawableFlag flag) => flag.Value;
        
        public bool IsEnable()
        {
            return Value;
        }

        public bool IsTrue()
        {
            return Value;
        }
    }
}
