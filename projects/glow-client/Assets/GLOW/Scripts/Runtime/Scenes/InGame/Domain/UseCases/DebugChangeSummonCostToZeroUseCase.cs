#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugChangeSummonCostToZeroUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void SetSummonCoolTimeToZero()
        {
            InGameScene.Debug = InGameScene.Debug with { IsZeroSummonCost = true };

            InGameScene.DeckUnits = InGameScene.DeckUnits
                .Select(deckUnit => deckUnit with { SummonCost = BattlePoint.Zero })
                .ToList();

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}
#endif //GLOW_INGAME_DEBUG
