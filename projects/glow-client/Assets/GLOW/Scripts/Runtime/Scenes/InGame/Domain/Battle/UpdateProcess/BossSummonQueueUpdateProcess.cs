using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class BossSummonQueueUpdateProcess : IBossSummonQueueUpdateProcess
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] ICharacterUnitFactory CharacterUnitFactory { get; }

        public BossSummonQueueUpdateProcessResult UpdateBossSummonQueue(
            IReadOnlyList<CharacterUnitModel> units,
            BossSummonQueueModel bossSummonQueue,
            BossAppearancePauseModel bossAppearancePause,
            IReadOnlyList<IAttackModel> attacks,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            TickCount tickCount)
        {
            var summonedCharacterUnit = CharacterUnitModel.Empty;
            var nextBoss = bossSummonQueue.NextBoss;
            var summonQueue = bossSummonQueue.SummonQueue;

            if (nextBoss.IsEmpty() && summonQueue.Count == 0)
            {
                return new BossSummonQueueUpdateProcessResult(
                    CharacterUnitModel.Empty,
                    units,
                    bossSummonQueue,
                    bossAppearancePause,
                    new List<IAttackModel>(),
                    attacks);
            }

            // 次に召喚するボスをセット
            if (nextBoss.IsEmpty() && bossSummonQueue.SummonQueue.Count > 0)
            {
                nextBoss = bossSummonQueue.SummonQueue[0];

                var summonQueueTmp = bossSummonQueue.SummonQueue.ToList();
                summonQueueTmp.RemoveAt(0);
                summonQueue = summonQueueTmp;
            }

            // ボス召喚までのフレーム数を更新。フレーム数が0になったらボスを召喚
            if (!nextBoss.IsEmpty())
            {
                summonedCharacterUnit = CreateBossUnit(nextBoss, komaDictionary);
                nextBoss = BossSummonQueueElement.Empty;
            }

            var updatedBossSummonQueue = new BossSummonQueueModel(
                nextBoss,
                summonQueue);

            if (summonedCharacterUnit.IsEmpty())
            {
                return new BossSummonQueueUpdateProcessResult(
                    CharacterUnitModel.Empty,
                    units,
                    updatedBossSummonQueue,
                    bossAppearancePause,
                    new List<IAttackModel>(),
                    attacks);
            }

            var updatedUnits = units.Append(summonedCharacterUnit).ToList();

            // ボス登場時は一定時間、登場ボス以外の敵などを一時停止する
            var pauseFrames = summonedCharacterUnit.AppearanceAttack.AttackDelay + InGameConstants.BossAppearanceKnockBackFrames;
            var appeardBossList = new List<FieldObjectId> {summonedCharacterUnit.Id};

            var updatedBossAppearancePause = new BossAppearancePauseModel(pauseFrames, appeardBossList);

            return new BossSummonQueueUpdateProcessResult(
                summonedCharacterUnit,
                updatedUnits,
                updatedBossSummonQueue,
                updatedBossAppearancePause,
                attacks,
                new List<IAttackModel>()); // 現時点で発生している攻撃を全て消去する
        }

        CharacterUnitModel CreateBossUnit(
            BossSummonQueueElement queueElement,
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
    }
}
