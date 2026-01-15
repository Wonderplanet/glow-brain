using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Models
{
    public record IdleIncentiveTopStageModel(
        IdleIncentiveTopPlayerUnitModel PlayerUnit,
        IdleIncentiveTopEnemyUnitModel EnemyUnit,
        KomaBackgroundAssetKey BackgroundAssetKey)
    {
        public static IdleIncentiveTopStageModel Empty { get; } = new(
            IdleIncentiveTopPlayerUnitModel.Empty,
            IdleIncentiveTopEnemyUnitModel.Empty,
            KomaBackgroundAssetKey.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
