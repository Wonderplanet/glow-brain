using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
#if GLOW_DEBUG
    public class DebugVictoryUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }

        public void Victory()
        {
            BattlePresenter.OnVictory(StageEndConditionType.EnemyOutpostBreakDown);
            InGameScene.IsBattleOver = BattleOverFlag.True;
        }
    }
#endif
}
