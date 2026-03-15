namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaMaxStepNumber(int Value)
    {
        public static StepUpGachaMaxStepNumber Empty { get; } = new StepUpGachaMaxStepNumber(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

