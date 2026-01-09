using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record DefeatResultModel(
        StageResultTips Tips,
        EnemyCountResultModel EnemyCountResult,
        InGameRetryModel InGameRetryModel
    );
}
