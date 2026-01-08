#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugToggleBattlePauseUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void SetBattlePause(bool isPaused)
        {
            InGameScene.Debug = InGameScene.Debug with { IsBattlePaused = isPaused };
        }
    }
}
#endif //GLOW_INGAME_DEBUG