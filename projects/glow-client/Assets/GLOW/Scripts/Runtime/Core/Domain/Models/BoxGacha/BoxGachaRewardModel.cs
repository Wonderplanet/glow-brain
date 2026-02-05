namespace GLOW.Core.Domain.Models.BoxGacha
{
    public record BoxGachaRewardModel(RewardModel Reward)
    {
        public static BoxGachaRewardModel Empty { get; } = new(RewardModel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}