using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IAdventBattleScoreModelFactory
    {
        ResultScoreModel CreateAdventBattleScoreModel(
            UserAdventBattleModel prevAdventBattleModel,
            EventBonusPercentage eventBonusPercentage);
    }
}