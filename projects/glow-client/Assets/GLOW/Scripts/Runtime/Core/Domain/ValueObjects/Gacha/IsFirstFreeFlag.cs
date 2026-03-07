namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record IsFirstFreeFlag(bool Value)
    {
        public static IsFirstFreeFlag True { get; } = new IsFirstFreeFlag(true);
        public static IsFirstFreeFlag False { get; } = new IsFirstFreeFlag(false);

        public static implicit operator bool(IsFirstFreeFlag flag)
        {
            return flag.Value;
        }

        public bool IsTrue()
        {
            return Value;
        }

        public bool IsFalse()
        {
            return !Value;
        }
    }
}

