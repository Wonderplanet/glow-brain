using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record ContinueDiamondModel(
        TotalDiamond Cost,
        PaidDiamond BeforePaidDiamond,
        FreeDiamond BeforeFreeDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond AfterFreeDiamond,
        bool IsLackOfDiamond,
        EnemyCountResultModel EnemyCountResult);
}
