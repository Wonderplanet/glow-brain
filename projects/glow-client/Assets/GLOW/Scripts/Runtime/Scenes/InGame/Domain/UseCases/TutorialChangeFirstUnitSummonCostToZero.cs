using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class TutorialChangeFirstUnitSummonCostToZero
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        
        public void ChangeFirstUnitSummonCostToZero()
        {
            var deckUnit = InGameScene.DeckUnits.FirstOrDefault(DeckUnitModel.Empty);

            var summonCostZeroFirstUnit = deckUnit with
            {
                SummonCost = BattlePoint.Zero
            };
            
            InGameScene.DeckUnits = InGameScene.DeckUnits.Replace(deckUnit, summonCostZeroFirstUnit);

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}