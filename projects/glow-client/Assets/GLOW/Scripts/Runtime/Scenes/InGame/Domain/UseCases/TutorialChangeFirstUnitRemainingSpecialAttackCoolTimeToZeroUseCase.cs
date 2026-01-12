using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class TutorialChangeFirstUnitRemainingSpecialAttackCoolTimeToZeroUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void ChangeFirstUnitRemainingSpecialAttackCoolTimeToZero()
        {
            var deckUnit = InGameScene.DeckUnits.FirstOrDefault(DeckUnitModel.Empty);
            
            var firstUnit = deckUnit with
            {
                RemainingSpecialAttackCoolTime = TickCount.Zero,
            };
            
            InGameScene.DeckUnits = InGameScene.DeckUnits.Replace(deckUnit, firstUnit);
            
            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}