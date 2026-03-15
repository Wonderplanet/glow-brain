namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaStepNumber(int Value)
    {
        public static StepUpGachaStepNumber Empty { get; } = new StepUpGachaStepNumber(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

