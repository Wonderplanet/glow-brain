using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BattlePointModel(
        BattlePoint MaxBattlePoint,
        BattlePoint CurrentBattlePoint,
        BattlePoint ChargeAmount,
        TickCount ChargeInterval,
        TickCount RemainingTickCountForCharge,
        bool IsMaxLevel)
    {
        public static BattlePointModel Empty { get; } = new(
            BattlePoint.Empty,
            BattlePoint.Empty,
            BattlePoint.Empty,
            TickCount.Empty,
            TickCount.Empty,
            false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
