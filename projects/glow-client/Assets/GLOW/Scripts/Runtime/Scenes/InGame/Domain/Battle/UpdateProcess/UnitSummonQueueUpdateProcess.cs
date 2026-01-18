using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class UnitSummonQueueUpdateProcess : IUnitSummonQueueUpdateProcess
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ICharacterUnitFactory CharacterUnitFactory { get; }

        public UnitSummonQueueUpdateProcessResult UpdateUnitSummonQueue(
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueueModel,
            DeckUnitSummonQueueModel deckUnitSummonQueueModel,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            TickCount tickCount)
        {
            var summonUnitModelList = new List<CharacterUnitModel>();
            var updatedUnitSummonQueueModel = unitSummonQueue;

            // 召喚されるボスがいないときだけ敵キャラ召喚
            if (!bossSummonQueueModel.ExistsQueuedBoss())
            {
                summonUnitModelList = unitSummonQueue.SummonQueue
                    .Select(element => CreateEnemyUnit(element, komaDictionary))
                    .ToList();

                updatedUnitSummonQueueModel = UnitSummonQueueModel.Empty;
            }

            // プレイヤーキャラ召喚
            summonUnitModelList.AddRange(deckUnitSummonQueueModel.SummonQueue
                .Where(element => element.BattleSide == BattleSide.Player)
                .Select(element => CreatePlayerUnit(element, komaDictionary))
                .ToList());

            // Pvpの対戦相手キャラ召喚
            summonUnitModelList.AddRange(deckUnitSummonQueueModel.SummonQueue
                .Where(element => element.BattleSide == BattleSide.Enemy)
                .Select(element => CreateOpponentUnit(element, komaDictionary))
                .ToList());

            var updatedDeckUnitSummonQueueModel = DeckUnitSummonQueueModel.Empty;

            // 召喚キャラを加えたリスト
            var updatedUnits = units.Concat(summonUnitModelList).ToList();

            return new UnitSummonQueueUpdateProcessResult(
                summonUnitModelList,
                updatedUnits,
                updatedUnitSummonQueueModel,
                updatedDeckUnitSummonQueueModel);
        }

        CharacterUnitModel CreateEnemyUnit(
            UnitSummonQueueElement queueElement,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            var mstCharacter = MstEnemyCharacterDataRepository.GetEnemyStageParameter(queueElement.EnemyId);

            var unit = CharacterUnitFactory.GenerateEnemyCharacterUnit(
                mstCharacter,
                queueElement.UnitGenerationModel,
                BattleSide.Enemy,
                komaDictionary,
                InGameScene.MstPage);

            return unit;
        }

        CharacterUnitModel CreatePlayerUnit(
            DeckUnitSummonQueueElement queueElement,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(queueElement.Id);

            var unit = CharacterUnitFactory.GenerateUserCharacterUnit(
                mstCharacter,
                BattleSide.Player,
                komaDictionary,
                InGameScene.MstPage);

            return unit;
        }

        CharacterUnitModel CreateOpponentUnit(
            DeckUnitSummonQueueElement queueElement,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(queueElement.Id);

            var unit = CharacterUnitFactory.GenerateOpponentCharacterUnit(
                mstCharacter,
                BattleSide.Enemy,
                komaDictionary,
                InGameScene.MstPage);

            return unit;
        }
    }
}
