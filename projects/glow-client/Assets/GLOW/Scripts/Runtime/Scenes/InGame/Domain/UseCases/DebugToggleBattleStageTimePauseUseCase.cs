using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

#if GLOW_INGAME_DEBUG
namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugToggleBattleStageTimePauseUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void SetBattleStageTimePause(bool isPaused)
        {
            InGameScene.Debug = InGameScene.Debug with
            {
                StageTimeSpeed = isPaused ? TickCount.Zero : TickCount.One
            };
        }
    }
}
#endif