using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class ExecuteRushUseCase
    {
        [Inject] IInGameScene InGameScene { get; }

        public void ExecuteRush(BattleSide battleSide)
        {
            // 総攻撃実行可能にする
            var rushModel = InGameScene.RushModel;
            if (battleSide == BattleSide.Enemy) rushModel = InGameScene.PvpOpponentRushModel;

            var executeRushModel = rushModel with
            {
                ExecuteRushFlag = ExecuteRushFlag.True
            };

            if (battleSide == BattleSide.Player) InGameScene.RushModel = executeRushModel;
            else InGameScene.PvpOpponentRushModel = executeRushModel;
        }
    }
}
