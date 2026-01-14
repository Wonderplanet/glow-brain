using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class PvpGiveUpUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void GiveUp()
        {
            InGameScene.IsBattleGiveUp = BattleGiveUpFlag.True;
        }
    }
}