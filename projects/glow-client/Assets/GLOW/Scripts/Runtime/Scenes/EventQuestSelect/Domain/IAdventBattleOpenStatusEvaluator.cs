using GLOW.Core.Domain.Models;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public interface IAdventBattleOpenStatusEvaluator
    {
        AdventBattleOpenStatus Evaluate(MstAdventBattleModel targetMstAdventBattleModel);
    }
}