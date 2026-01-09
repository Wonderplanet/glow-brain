using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.AdventBattleMission.Domain.Evaluator
{
    public interface IAdventBattleDateTimeEvaluator
    {
        MstAdventBattleModel GetOpenedAdventBattleModel();
    }
}