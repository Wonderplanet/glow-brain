using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record InGameRetryModel(
        RetryAvailableFlag IsRetryAvailable,
        AdChallengeFlag IsAdChallenge)
    {
        public static InGameRetryModel Empty { get; } = new(
            RetryAvailableFlag.False,
            AdChallengeFlag.False);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}