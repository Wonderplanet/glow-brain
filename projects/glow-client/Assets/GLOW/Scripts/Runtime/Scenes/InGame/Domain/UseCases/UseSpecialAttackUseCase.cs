using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class UseSpecialAttackUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IDeckUnitSpecialAttackEvaluator DeckUnitSpecialAttackEvaluator { get; }
        [Inject] IDeckUnitSpecialAttackExecutor DeckUnitSpecialAttackExecutor { get; }

        public void UseSpecialAttack(MasterDataId characterId)
        {
            if (InGameScene.IsBattleOver) return;

            var deckUnitModel = InGameScene.DeckUnits.FirstOrDefault(
                model => model.CharacterId == characterId,
                DeckUnitModel.Empty);

            if (!DeckUnitSpecialAttackEvaluator.CanUseSpecialAttack(deckUnitModel)) return;

            var result = DeckUnitSpecialAttackExecutor.UseSpecialAttack(
                deckUnitModel,
                InGameScene.CharacterUnits,
                BattleSide.Player);
            if (result.IsEmpty()) return;

            InGameScene.CharacterUnits = InGameScene.CharacterUnits.Replace(result.Unit, result.UpdatedUnit);
            InGameScene.DeckUnits = InGameScene.DeckUnits.Replace(deckUnitModel, result.UpdatedDeckUnit);

            BattlePresenter.OnUpdateDeck(InGameScene.DeckUnits, InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}
