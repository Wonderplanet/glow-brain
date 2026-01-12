using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
#if GLOW_DEBUG
    public class DebugDefeatUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }

        public void Defeat()
        {
            var canContinue = !InGameScene.IsContinued && !InGameScene.IsNoContinue;
            if (canContinue)
            {
                BattlePresenter.OnDefeatWithContinue(StageEndConditionType.PlayerOutpostBreakDown);
            }
            else
            {
                BattlePresenter.OnDefeatCannotContinue(StageEndConditionType.PlayerOutpostBreakDown);
            }
            
            InGameScene.IsBattleOver = BattleOverFlag.True;
        }
    }
#endif
}
