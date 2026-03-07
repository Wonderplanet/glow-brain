using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class CheckOpenAdventBattleUseCase
    {
        [Inject] IAdventBattleDateTimeEvaluator AdventBattleDateTimeEvaluator { get; }
        
        public bool IsOpen()
        {
            return !AdventBattleDateTimeEvaluator.GetOpenedAdventBattleModel().IsEmpty();
        }
    }
}