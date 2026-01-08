using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class SummonUserCharacterUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IDeckUnitSummonEvaluator DeckUnitSummonEvaluator { get; }
        [Inject] IDeckUnitSummonExecutor DeckUnitSummonExecutor { get; }

        public void SummonCharacter(BattleSide battleSide, MasterDataId unitId)
        {
            if (InGameScene.IsBattleOver) return;

            if (battleSide == BattleSide.Player)
            {
                var battlePointModel = InGameScene.BattlePointModel;

                DeckUnitModel deckUnit =
                    InGameScene.DeckUnits.FirstOrDefault(model => model.CharacterId == unitId, DeckUnitModel.Empty);

                if (!DeckUnitSummonEvaluator.CanSummon(deckUnit, battlePointModel)) return;

                var result = DeckUnitSummonExecutor.Summon(deckUnit, battlePointModel);

                InGameScene.BattlePointModel = result.UpdatedBattlePointModel;
                InGameScene.DeckUnits = InGameScene.DeckUnits.Replace(deckUnit, result.UpdatedDeckUnit);

                BattlePresenter.OnUpdateBattlePoint(result.UpdatedBattlePointModel);
                BattlePresenter.OnUpdateDeck(InGameScene.DeckUnits, result.UpdatedBattlePointModel.CurrentBattlePoint);
            }

            // 召喚キューに入れる
            var summonQueueElement = new DeckUnitSummonQueueElement(unitId, battleSide);
            InGameScene.DeckUnitSummonQueue = InGameScene.DeckUnitSummonQueue with
            {
                SummonQueue = InGameScene.DeckUnitSummonQueue.SummonQueue.ToList().ChainAdd(summonQueueElement)
            };
        }
    }
}
