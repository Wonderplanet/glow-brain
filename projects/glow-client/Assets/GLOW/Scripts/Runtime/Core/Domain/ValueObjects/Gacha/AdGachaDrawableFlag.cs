namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record AdGachaDrawableFlag(bool Value)
    {
        public static AdGachaDrawableFlag Empty { get; } = new(false);
        public static AdGachaDrawableFlag True { get; } = new(true);
        public static AdGachaDrawableFlag False { get; } = new(false);

        public static implicit operator bool(AdGachaDrawableFlag flag) => flag.Value;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public DrawableFlag ToDrawableFlag()
        {
            return new DrawableFlag(Value);
        }
    }
}