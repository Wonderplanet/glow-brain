using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record ContinueActionSelectionModel(
        TotalDiamond Cost,
        ContinueCount RemainingContinueAdCount,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfo,
        EnemyCountResultModel EnemyCountResult);
}
