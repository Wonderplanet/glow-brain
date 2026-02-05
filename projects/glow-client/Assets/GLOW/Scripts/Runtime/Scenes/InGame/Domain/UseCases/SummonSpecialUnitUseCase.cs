using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    /// <summary> ロールがスペシャルのユニット用の召喚キュー追加処理 </summary>
    public class SummonSpecialUnitUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IDeckSpecialUnitSummonExecutor DeckSpecialUnitSummonExecutor { get; }
        [Inject] IDeckSpecialUnitSummonPositionSelector DeckSpecialUnitSummonPositionSelector { get; }

        public bool TryAddUnitSummonQueue(BattleSide battleSide, MasterDataId characterId, PageCoordV2 pos)
        {
            // 召喚位置選択は終了
            InGameScene.SpecialUnitSummonInfoModel = InGameScene.SpecialUnitSummonInfoModel with
            {
                IsSummonPositionSelecting = SpecialUnitSummonPositionSelectingFlag.False
            };

            if (InGameScene.IsBattleOver) return false;

            var summonPos = pos;

            if (battleSide == BattleSide.Player)
            {
                DeckUnitModel deckUnit = InGameScene.DeckUnits.FirstOrDefault(
                    model => model.CharacterId == characterId,
                    DeckUnitModel.Empty);

                if (deckUnit.IsEmpty()) return false;

                var canSummon = DeckSpecialUnitSummonEvaluator.CanSummon(
                    deckUnit,
                    InGameScene.BattlePointModel,
                    InGameScene.SpecialUnitSummonInfoModel,
                    InGameScene.SpecialUnits,
                    InGameScene.SpecialUnitSummonQueue,
                    battleSide);

                if (!canSummon) return false;

                // 召喚位置を決める
                summonPos = DeckSpecialUnitSummonPositionSelector.SelectSummonPosition(
                    pos,
                    deckUnit.SpecialAttackData,
                    InGameScene.SpecialUnitSummonInfoModel,
                    InGameScene.MstPage,
                    InGameScene.KomaDictionary,
                    CoordinateConverter,
                    battleSide);

                if (summonPos.IsEmpty()) return false;

                // 召喚処理
                var result = DeckSpecialUnitSummonExecutor.Summon(deckUnit, InGameScene.BattlePointModel);

                InGameScene.BattlePointModel = result.UpdatedBattlePointModel;
                InGameScene.DeckUnits = InGameScene.DeckUnits.Replace(deckUnit, result.UpdatedDeckUnit);

                BattlePresenter.OnUpdateBattlePoint(result.UpdatedBattlePointModel);
                BattlePresenter.OnUpdateDeck(InGameScene.DeckUnits, result.UpdatedBattlePointModel.CurrentBattlePoint);
            }

            // 召喚キューに入れる
            var summonQueueElement = new SpecialUnitSummonQueueElement(battleSide, characterId, summonPos);
            InGameScene.SpecialUnitSummonQueue = InGameScene.SpecialUnitSummonQueue with
            {
                SummonQueue = InGameScene.SpecialUnitSummonQueue.SummonQueue.ToList().ChainAdd(summonQueueElement)
            };

            return true;
        }
    }
}
