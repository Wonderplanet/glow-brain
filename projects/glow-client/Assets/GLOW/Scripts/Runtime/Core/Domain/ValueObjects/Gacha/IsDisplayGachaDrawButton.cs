namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record IsDisplayGachaDrawButton(bool Value)
    {
        public static IsDisplayGachaDrawButton True => new(true);
        public static IsDisplayGachaDrawButton False => new(false);
        
        public static implicit operator bool(IsDisplayGachaDrawButton flag) => flag.Value;

        public bool IsTrue()
        {
            return Value;
        }
    }
}
