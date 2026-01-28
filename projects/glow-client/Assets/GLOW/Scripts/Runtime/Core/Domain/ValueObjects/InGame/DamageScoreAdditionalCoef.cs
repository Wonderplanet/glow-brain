using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record DamageScoreAdditionalCoef(decimal Value)
    {
        public static DamageScoreAdditionalCoef Empty { get; } = new(0);
        public static DamageScoreAdditionalCoef One { get; } = new(1); // 降臨バトル以外は基本こちらを使用する

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
