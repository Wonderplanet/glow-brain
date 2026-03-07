namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaCurrentLoopCount(int Value)
    {
        public static StepUpGachaCurrentLoopCount Empty { get; } = new StepUpGachaCurrentLoopCount(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

