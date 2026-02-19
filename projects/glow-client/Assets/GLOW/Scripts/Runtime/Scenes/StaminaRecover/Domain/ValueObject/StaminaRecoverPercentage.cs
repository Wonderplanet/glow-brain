namespace GLOW.Scenes.StaminaRecover.Domain.ValueObject
{
    public record StaminaRecoverPercentage(int Value)
    {
        public static StaminaRecoverPercentage Empty { get; } = new StaminaRecoverPercentage(0);

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}