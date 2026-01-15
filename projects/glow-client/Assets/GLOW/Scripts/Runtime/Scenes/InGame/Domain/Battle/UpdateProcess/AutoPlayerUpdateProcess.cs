using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class AutoPlayerUpdateProcess : IAutoPlayerUpdateProcess
    {
        [Inject(Id = Battle.AutoPlayer.AutoPlayer.PlayerAutoPlayerBindId)] IAutoPlayer PlayerAutoPlayer { get; }
        [Inject(Id = Battle.AutoPlayer.AutoPlayer.EnemyAutoPlayerBindId)] IAutoPlayer EnemyAutoPlayer { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] ICharacterUnitFactory CharacterUnitFactory { get; }
        [Inject] IUnitGenerationModelFactory UnitGenerationModelFactory { get; }
        [Inject] IDeckUnitSummonEvaluator DeckUnitSummonEvaluator { get; }
        [Inject] IDeckUnitSummonExecutor DeckUnitSummonExecutor { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IDeckSpecialUnitSummonExecutor DeckSpecialUnitSummonExecutor { get; }
        [Inject] IDeckUnitSpecialAttackEvaluator DeckUnitSpecialAttackEvaluator { get; }
        [Inject] IDeckUnitSpecialAttackExecutor DeckUnitSpecialAttackExecutor { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public AutoPlayerUpdateProcessResult UpdateAutoPlayer(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            IReadOnlyList<GimmickObjectToEnemyTransformationModel> gimmickObjectToEnemyTransformationModels,
            DefeatEnemyCount totalDeadEnemyCount,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueue,
            DeckUnitSummonQueueModel deckUnitSummonQueue,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage,
            BattlePointModel battlePoint,
            BattlePointModel pvpOpponentBattlePoint,
            StageTimeModel stageTime,
            RushModel pvpOpponentRushModel,
            TickCount tickCount)
        {
            var summonedUnitList = new List<CharacterUnitModel>();
            var updatedUnitList = units;
            var updatedBossQueue = bossSummonQueue;
            var updatedUnitQueue = unitSummonQueue;
            var updatedDeckUnitSummonQueue = deckUnitSummonQueue;
            var updatedSpecialUnitSummonQueue = specialUnitSummonQueue;
            var updatedGimmickObjectToEnemyTransformationModels = gimmickObjectToEnemyTransformationModels;
            var updatedDeckUnits = deckUnits;
            var updatedBattlePointModel = battlePoint;
            var updatedPvpOpponentDeckUnits = pvpOpponentDeckUnits;
            var updatedPvpOpponentBattlePointModel = pvpOpponentBattlePoint;

            // AutoPlayerを更新
            var commonConditionContext = new CommonConditionContext(
                CharacterUnitModel.Empty,
                units,
                deadUnits,
                totalDeadEnemyCount,
                playerOutpost,
                enemyOutpost,
                stageTime,
                komaDictionary,
                mstPage,
                EnemyAutoPlayer.CurrentAutoPlayerSequenceGroupModel);

            var autoPlayerTickContext = new AutoPlayerTickContext(
                battlePoint,
                pvpOpponentBattlePoint,
                deckUnits,
                pvpOpponentDeckUnits,
                units,
                specialUnits,
                specialUnitSummonInfo,
                specialUnitSummonQueue,
                komaDictionary,
                mstPage,
                UnitGenerationModelFactory,
                CoordinateConverter,
                commonConditionContext,
                pvpOpponentRushModel,
                tickCount);

            IReadOnlyList<IAutoPlayerAction> playerActions = PlayerAutoPlayer.IsEnabled
                ? PlayerAutoPlayer.Tick(autoPlayerTickContext)
                : new List<IAutoPlayerAction>();

            IReadOnlyList<IAutoPlayerAction> enemyActions = EnemyAutoPlayer.IsEnabled
                ? EnemyAutoPlayer.Tick(autoPlayerTickContext)
                : new List<IAutoPlayerAction>();

            // 召喚する敵キャラのMstEnemyCharacterModelを取得
            var enemyTupleList = enemyActions
                .Select(action => action.ToSummonEnemyAction())
                .Where(action => !action.IsEmpty())
                .Select(action => (
                    Action:action,
                    MstEnemyCharacterModel:MstEnemyCharacterDataRepository.GetEnemyStageParameter(action.EnemyId)))
                .ToList();

            // ボスは召喚キューに入れる
            foreach (var enemyTuple in enemyTupleList)
            {
                if (!enemyTuple.MstEnemyCharacterModel.IsBoss) continue;

                updatedBossQueue = EnqueueBoss(
                    enemyTuple.MstEnemyCharacterModel,
                    enemyTuple.Action.UnitGenerationModel,
                    updatedBossQueue);
            }

            if (updatedBossQueue.ExistsQueuedBoss())
            {
                // ボスの召喚待ちの間は敵キャラを召喚キューに入れる
                var queueElementList = enemyTupleList
                    .Where(enemyTuple => !enemyTuple.MstEnemyCharacterModel.IsBoss)
                    .Select(enemyTuple => new UnitSummonQueueElement(
                        enemyTuple.MstEnemyCharacterModel.Id,
                        enemyTuple.Action.UnitGenerationModel))
                    .ToList();

                updatedUnitQueue = updatedUnitQueue with
                {
                    SummonQueue = updatedUnitQueue.SummonQueue.Concat(queueElementList).ToList()
                };
            }
            else
            {
                // 敵キャラの召喚
                var enemyUnitModelList = enemyTupleList
                    .Where(tuple => !tuple.MstEnemyCharacterModel.IsBoss)
                    .Select(tuple => CreateEnemyCharacterUnitModel(
                        tuple.MstEnemyCharacterModel,
                        tuple.Action.UnitGenerationModel,
                        mstPage,
                        komaDictionary));

                summonedUnitList.AddRange(enemyUnitModelList);
            }

            // プレイヤーキャラの召喚
            var updateDeckUnitSummon = UpdateDeckUnitSummon(
                playerActions,
                updatedDeckUnits,
                updatedBattlePointModel,
                updatedDeckUnitSummonQueue,
                BattleSide.Player);
            updatedDeckUnits = updateDeckUnitSummon.updatedDeckUnits;
            updatedBattlePointModel = updateDeckUnitSummon.updatedBattlePointModel;
            updatedDeckUnitSummonQueue = updateDeckUnitSummon.updatedUnitSummonQueue;

            // プレイヤー側スペシャルキャラの召喚
            var updateDeckSpecialUnitSummon = UpdateDeckSpecialUnitSummon(
                playerActions,
                updatedDeckUnits,
                updatedBattlePointModel,
                specialUnits,
                updatedSpecialUnitSummonQueue,
                specialUnitSummonInfo,
                mstPage,
                komaDictionary,
                BattleSide.Player);
            updatedDeckUnits = updateDeckSpecialUnitSummon.updatedDeckUnits;
            updatedBattlePointModel = updateDeckSpecialUnitSummon.updatedBattlePointModel;
            updatedSpecialUnitSummonQueue = updateDeckSpecialUnitSummon.updatedSpecialUnitSummonQueue;

            // Pvpの対戦相手のユニット召喚
            var updateOpponentUnitSummon = UpdateDeckUnitSummon(
                enemyActions,
                updatedPvpOpponentDeckUnits,
                updatedPvpOpponentBattlePointModel,
                updatedDeckUnitSummonQueue,
                BattleSide.Enemy);
            updatedPvpOpponentDeckUnits = updateOpponentUnitSummon.updatedDeckUnits;
            updatedPvpOpponentBattlePointModel = updateOpponentUnitSummon.updatedBattlePointModel;
            updatedDeckUnitSummonQueue = updateOpponentUnitSummon.updatedUnitSummonQueue;

            // Pvpの対戦相手のスペシャルユニット召喚
            var updateOpponentSpecialUnitSummon = UpdateDeckSpecialUnitSummon(
                enemyActions,
                updatedPvpOpponentDeckUnits,
                updatedPvpOpponentBattlePointModel,
                specialUnits,
                updatedSpecialUnitSummonQueue,
                specialUnitSummonInfo,
                mstPage,
                komaDictionary,
                BattleSide.Enemy);
            updatedPvpOpponentDeckUnits = updateOpponentSpecialUnitSummon.updatedDeckUnits;
            updatedPvpOpponentBattlePointModel = updateOpponentSpecialUnitSummon.updatedBattlePointModel;
            updatedSpecialUnitSummonQueue = updateOpponentSpecialUnitSummon.updatedSpecialUnitSummonQueue;

            // ギミック->敵変換
            var transformGimmickObjectToEnemyActionList = enemyActions
                .Select(action => action.ToTransformGimmickObjectToEnemyAction())
                .Where(action => !action.IsEmpty())
                .ToList();

            var addTransformationModels = new List<GimmickObjectToEnemyTransformationModel>();
            foreach (var gimmickToEnemyAction in transformGimmickObjectToEnemyActionList)
            {
                var transformationModel = new GimmickObjectToEnemyTransformationModel(
                    gimmickToEnemyAction.EnemyId,
                    gimmickToEnemyAction.UnitGenerationModel,
                    gimmickToEnemyAction.TransformTargetGimmickSequenceElementId
                );

                addTransformationModels.Add(transformationModel);
            }

            updatedGimmickObjectToEnemyTransformationModels =
                updatedGimmickObjectToEnemyTransformationModels.Concat(addTransformationModels).ToList();

            // プレイヤーキャラの必殺ワザ使用
            var updateDeckSpecialAttack = UpdateDeckSpecialAttack(
                playerActions,
                updatedUnitList,
                updatedDeckUnits,
                BattleSide.Player);
            updatedUnitList = updateDeckSpecialAttack.updatedUnits;
            updatedDeckUnits = updateDeckSpecialAttack.updatedDeckUnits;

            // Pvpの対戦相手キャラの必殺ワザ使用
            var updateOpponentDeckSpecialAttack = UpdateDeckSpecialAttack(
                enemyActions,
                updatedUnitList,
                updatedPvpOpponentDeckUnits,
                BattleSide.Enemy);
            updatedUnitList = updateOpponentDeckSpecialAttack.updatedUnits;
            updatedPvpOpponentDeckUnits = updateOpponentDeckSpecialAttack.updatedDeckUnits;

            // 更新されたキャラリスト
            updatedUnitList = updatedUnitList.Concat(summonedUnitList).ToList();

            // Pvpの対戦相手Rushモデル更新
            var updatePvpOpponentRushModel = UpdatePvpOpponentRushModel(
                enemyActions,
                pvpOpponentRushModel);

            return new AutoPlayerUpdateProcessResult(
                summonedUnitList,
                updatedUnitList,
                updatedGimmickObjectToEnemyTransformationModels,
                updatedUnitQueue,
                updatedBossQueue,
                updatedDeckUnitSummonQueue,
                updatedSpecialUnitSummonQueue,
                updatedDeckUnits,
                updatedPvpOpponentDeckUnits,
                updatedBattlePointModel,
                updatedPvpOpponentBattlePointModel,
                updatePvpOpponentRushModel);
        }

        /// <summary> プレイヤーもしくはPvpの対戦相手用のユニット召喚 </summary>
        (IReadOnlyList<DeckUnitModel> updatedDeckUnits,
        BattlePointModel updatedBattlePointModel,
        DeckUnitSummonQueueModel updatedUnitSummonQueue)
        UpdateDeckUnitSummon(
            IReadOnlyList<IAutoPlayerAction> playerActions,
            IReadOnlyList<DeckUnitModel> deckUnits,
            BattlePointModel battlePointModel,
            DeckUnitSummonQueueModel unitSummonQueue,
            BattleSide battleSide)
        {
            var updatedDeckUnits = deckUnits.ToList();
            var updatedBattlePointModel = battlePointModel;
            var updatedOpponentUnitSummonQueue = unitSummonQueue;

            var deckUnitSummonActions = playerActions
                .Select(action => action.ToSummonDeckUnitAction())
                .Where(action => !action.IsEmpty());

            foreach (var action in deckUnitSummonActions)
            {
                var deckUnit = GetDeckUnit(updatedDeckUnits, action.DeckUnitIndex);

                if (!DeckUnitSummonEvaluator.CanSummon(deckUnit, updatedBattlePointModel)) continue;

                var result = DeckUnitSummonExecutor.Summon(deckUnit, updatedBattlePointModel);

                updatedBattlePointModel = result.UpdatedBattlePointModel;
                updatedDeckUnits = updatedDeckUnits.Replace(deckUnit, result.UpdatedDeckUnit);

                // 召喚キューに入れる
                var summonQueueElement = new DeckUnitSummonQueueElement(deckUnit.CharacterId, battleSide);
                updatedOpponentUnitSummonQueue = updatedOpponentUnitSummonQueue with
                {
                    SummonQueue = updatedOpponentUnitSummonQueue.SummonQueue.ToList().ChainAdd(summonQueueElement)
                };
            }

            return (updatedDeckUnits, updatedBattlePointModel, updatedOpponentUnitSummonQueue);
        }

        /// <summary> プレイヤーもしくはPvpの対戦相手用のスペシャルキャラ召喚 </summary>
        (IReadOnlyList<DeckUnitModel> updatedDeckUnits,
        BattlePointModel updatedBattlePointModel,
        SpecialUnitSummonQueueModel updatedSpecialUnitSummonQueue)
        UpdateDeckSpecialUnitSummon(
            IReadOnlyList<IAutoPlayerAction> playerActions,
            IReadOnlyList<DeckUnitModel> deckUnits,
            BattlePointModel battlePointModel,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            BattleSide battleSide)
        {
            var updatedDeckUnits = deckUnits.ToList();
            var updatedBattlePointModel = battlePointModel;
            var updatedSpecialUnitSummonQueue = specialUnitSummonQueue;

            var specialUnitSummonActions = playerActions
                .Select(action => action.ToSummonDeckSpecialUnitAction())
                .Where(action => !action.IsEmpty());

            foreach (var action in specialUnitSummonActions)
            {
                var deckUnit = GetDeckUnit(updatedDeckUnits, action.DeckUnitIndex);

                // 召喚できる状態かチェック
                var canSummon = DeckSpecialUnitSummonEvaluator.CanSummon(
                    deckUnit,
                    updatedBattlePointModel,
                    specialUnitSummonInfo,
                    specialUnits,
                    specialUnitSummonQueue,
                    battleSide);

                if (!canSummon) continue;

                // 召喚可能な位置かチェック
                var isSummonablePosition = DeckSpecialUnitSummonEvaluator.IsSummonablePosition(
                    action.SummonPosition,
                    specialUnitSummonInfo,
                    mstPage,
                    komaDictionary,
                    CoordinateConverter,
                    battleSide);

                if (!isSummonablePosition) continue;

                // 召喚処理
                var result = DeckSpecialUnitSummonExecutor.Summon(deckUnit, updatedBattlePointModel);

                updatedBattlePointModel = result.UpdatedBattlePointModel;
                updatedDeckUnits = updatedDeckUnits.Replace(deckUnit, result.UpdatedDeckUnit);

                // 召喚キューに入れる
                var summonQueueElement = new SpecialUnitSummonQueueElement(
                    battleSide,
                    deckUnit.CharacterId,
                    action.SummonPosition);

                updatedSpecialUnitSummonQueue = updatedSpecialUnitSummonQueue with
                {
                    SummonQueue = updatedSpecialUnitSummonQueue.SummonQueue.ToList().ChainAdd(summonQueueElement)
                };

                // 複数のスペシャルキャラを同時に召喚はさせない
                break;
            }

            return (updatedDeckUnits, updatedBattlePointModel, updatedSpecialUnitSummonQueue);
        }

        /// <summary> プレイヤーもしくはPvpの対戦相手用のキャラの必殺ワザ使用 </summary>
        (IReadOnlyList<DeckUnitModel> updatedDeckUnits, IReadOnlyList<CharacterUnitModel> updatedUnits) UpdateDeckSpecialAttack(
            IReadOnlyList<IAutoPlayerAction> playerActions,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<DeckUnitModel> deckUnits,
            BattleSide battleSide)
        {
            var updatedUnitList = units;
            var updatedDeckUnits = deckUnits.ToList();

            var deckSpecialAttackActions = playerActions
                .Select(action => action.ToDeckSpecialAttackAction())
                .Where(action => !action.IsEmpty());

            foreach (var action in deckSpecialAttackActions)
            {
                var deckUnit = GetDeckUnit(updatedDeckUnits, action.DeckUnitIndex);

                if (!DeckUnitSpecialAttackEvaluator.CanUseSpecialAttack(deckUnit)) continue;

                var result = DeckUnitSpecialAttackExecutor.UseSpecialAttack(
                    deckUnit,
                    updatedUnitList,
                    battleSide);
                if (result.IsEmpty()) continue;

                updatedUnitList = updatedUnitList.Replace(result.Unit, result.UpdatedUnit);
                updatedDeckUnits = updatedDeckUnits.Replace(deckUnit, result.UpdatedDeckUnit);
            }

            return (updatedDeckUnits, updatedUnitList);
        }

        DeckUnitModel GetDeckUnit(IReadOnlyList<DeckUnitModel> deckUnits, DeckUnitIndex deckUnitIndex)
        {
            if (deckUnitIndex.IsEmpty()) return DeckUnitModel.Empty;

            int index = deckUnitIndex.Value;
            if (index < 0 || index >= deckUnits.Count) return DeckUnitModel.Empty;

            return deckUnits[index];
        }

        CharacterUnitModel CreateEnemyCharacterUnitModel(
            MstEnemyStageParameterModel mstEnemyStageParameterModel,
            UnitGenerationModel unitGenerationModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            return CharacterUnitFactory.GenerateEnemyCharacterUnit(
                mstEnemyStageParameterModel,
                unitGenerationModel,
                BattleSide.Enemy,
                komaDictionary,
                mstPage);
        }

        BossSummonQueueModel EnqueueBoss(
            MstEnemyStageParameterModel mstEnemyStageParameterModel,
            UnitGenerationModel unitGenerationModel,
            BossSummonQueueModel bossSummonQueue)
        {
            if (!mstEnemyStageParameterModel.IsBoss) return bossSummonQueue;

            var queueElement = new BossSummonQueueElement(mstEnemyStageParameterModel.Id, unitGenerationModel);

            var updatedBossSummonQueue = bossSummonQueue with
            {
                SummonQueue = new List<BossSummonQueueElement>(bossSummonQueue.SummonQueue).ChainAdd(queueElement)
            };

            return updatedBossSummonQueue;
        }

        RushModel UpdatePvpOpponentRushModel(
            IReadOnlyList<IAutoPlayerAction> enemyActions,
            RushModel pvpOpponentRushModel)
        {
            // Rush発動の更新
            var opponentExecuteRush = enemyActions
                .Select(action => action.ToOpponentRushAction())
                .FirstOrDefault(action => !action.IsEmpty());
            var updatePvpOpponentRushModel = pvpOpponentRushModel;
            if (opponentExecuteRush != null)
            {
                updatePvpOpponentRushModel = pvpOpponentRushModel with
                {
                    ExecuteRushFlag = opponentExecuteRush.ExecuteRushFlag
                };
            }

            return updatePvpOpponentRushModel;
        }
    }
}
