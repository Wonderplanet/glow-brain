namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaCurrentStepNumber(int Value)
    {
        public static StepUpGachaCurrentStepNumber Empty { get; } = new StepUpGachaCurrentStepNumber(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

