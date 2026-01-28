namespace GLOW.Core.Domain.Models.ExchangeShop
{
    public record ExchangeRewardModel(RewardModel ExchangeReward)
    {
        public static ExchangeRewardModel Empty { get; } = new(
            RewardModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
