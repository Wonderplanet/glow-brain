#if GLOW_DEBUG
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugMaximizeBattlePointUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void MaximizeBattlePoint()
        {
            BattlePointModel battlePointModel = InGameScene.BattlePointModel;

            // BattlePointを最大値にする
            var updatedBattlePointModel = battlePointModel with { CurrentBattlePoint = battlePointModel.MaxBattlePoint };

            InGameScene.BattlePointModel = updatedBattlePointModel;

            BattlePresenter.OnUpdateBattlePoint(updatedBattlePointModel);

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}
#endif //GLOW_DEBUG
