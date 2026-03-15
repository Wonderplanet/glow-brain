namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record StepUpStepCount(
        int CurrentStepNumber,
        int MaxStepNumber,
        int CurrentLoopCount,
        int MaxLoopCount)
    {
        public static StepUpStepCount Empty { get; } = new StepUpStepCount(0, 0, 0, 0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

