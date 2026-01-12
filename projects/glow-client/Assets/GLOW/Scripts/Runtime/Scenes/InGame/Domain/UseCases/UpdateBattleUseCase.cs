using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.Logger;
using GLOW.Scenes.InGame.Domain.Battle.UpdateProcess;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;
using Zenject;

#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.Repositories.Debug;
#endif

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class UpdateBattleUseCase
    {
        record UpdateCharacterUnitsResultModel(IReadOnlyList<FieldObjectId> BlockedUnits);

        record ExecuteAttacksResultModel(
            IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults,
            IReadOnlyList<FieldObjectId> BlockedUnits);

        record UpdateKomaEffectsResultModel(IReadOnlyList<FieldObjectId> BlockedUnits);

        record RemoveUnitsResultModel(
            IReadOnlyList<CharacterUnitModel> DeadUnits,
            IReadOnlyList<SpecialUnitModel> RemovedSpecialUnits);

        [Inject] IInGameScene InGameScene { get; }
        [Inject] ICharacterUnitUpdateProcess CharacterUnitUpdateProcess { get; }
        [Inject] ISpecialUnitUpdateProcess SpecialUnitUpdateProcess { get; }
        [Inject] IAttackProcess AttackProcess { get; }
        [Inject] IStateEffectUpdateProcess StateEffectUpdateProcess { get; }
        [Inject] IBattlePointUpdateProcess BattlePointUpdateProcess { get; }
        [Inject] IAutoPlayerUpdateProcess AutoPlayerUpdateProcess { get; }
        [Inject] IRushUpdateProcess RushUpdateProcess { get; }
        [Inject] IKomaEffectProcess KomaEffectProcess { get; }
        [Inject] IUnitAbilityProcess UnitAbilityProcess { get; }
        [Inject] IBossSummonQueueUpdateProcess BossSummonQueueUpdateProcess { get; }
        [Inject] IUnitSummonQueueUpdateProcess UnitSummonQueueUpdateProcess { get; }
        [Inject] ISpecialUnitSummonQueueUpdateProcess SpecialUnitSummonQueueUpdateProcess { get; }
        [Inject] IGimmickObjectToEnemyTransformationUpdateProcess GimmickObjectToEnemyTransformationUpdateProcess { get; }
        [Inject] IMangaAnimationUpdateProcess MangaAnimationUpdateProcess { get; }
        [Inject] IBossAppearancePauseUpdateProcess BossAppearancePauseUpdateProcess { get; }
        [Inject] IUnitRemovingProcess UnitRemovingProcess { get; }
        [Inject] IGimmickObjectRemovingProcess GimmickObjectRemovingProcess { get; }
        [Inject] IDeckUpdateProcess DeckUpdateProcess { get; }
        [Inject] IUnitTransformationProcess UnitTransformationProcess { get; }
        [Inject] ISpeechBalloonProcess SpeechBalloonProcess { get; }
        [Inject] IStageTimeUpdateProcess StageTimeUpdateProcess { get; }
        [Inject] IBattleEndCheckProcess BattleEndCheckProcess { get; }
        [Inject] IScoreUpdateProcess ScoreUpdateProcess { get; }
        [Inject] IUpdatePlacedItemsProcess UpdatePlacedItemsProcess { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameLogger InGameLogger { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }


#if GLOW_INGAME_DEBUG
        [Inject] IInGameDebugReportRepository DebugReportRepository { get; }
#endif

        readonly TickCount _tickCountUnit = new(1);

        public void Tick()
        {
            if (InGameScene.IsBattleOver) return;

            // 現在TickCountと制限時間の更新
            UpdateStageTime();

            // ボス登場時の一時停止の更新
            UpdateBossAppearancePause();

            // AIの行動
            UpdateAutoPlayer();

            // ギミック->敵変換キューの更新(敵召喚キューの追加が入るためここに)
            UpdateTransformGimmickObjectToEnemyQueue();

            // ボス召喚キューの更新
            UpdateBossSummonQueue();

            // ユニット召喚キューの更新
            UpdateUnitSummonQueue();

            // ロールがスペシャルのユニット召喚キューの更新
            UpdateSpecialUnitSummonQueue();

            // 状態効果の更新
            UpdateStateEffects();

            // 総攻撃の更新
            UpdateRush();

            // スペシャルキャラ更新
            UpdateSpecialUnits();

            // キャラ更新
            var updateCharacterUnitsResult = UpdateCharacterUnits();

            // キャラ特性の更新
            UpdateUnitAbilities();

            // コマ効果の更新
            var updateKomaEffectsResult = UpdateKomaEffects();

            // 攻撃処理
            var executeAttackResults = ExecuteAttacks();

            // フィールド上のオブジェクトを各種更新
            Profiler.BeginSample("OnUpdateFieldObjects");
            BattlePresenter.OnUpdateFieldObjects(
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.CharacterUnits,
                InGameScene.SpecialUnits,
                InGameScene.DefenseTarget,
                executeAttackResults.AppliedAttackResults);
            Profiler.EndSample();

            // ブロックが発生したUnitをPresenterへ伝える
            UpdateEffectBlocked(updateCharacterUnitsResult, updateKomaEffectsResult, executeAttackResults);

            // 吹き出し
            Speak();

            // マンガ演出更新
            UpdateMangaAnimations();

            // 変身
            TransformUnits();

            // アイテム配置、削除
            UpdatePlacedItems();

            // キャラの削除
            var removeUnitsResults = RemoveUnits();

            // ギミックオブジェクトの削除
            RemoveGimmickObject();

            // スコア加算処理
            UpdateScore(removeUnitsResults.DeadUnits, executeAttackResults.AppliedAttackResults);

            // バトルポイント更新
            UpdateBattlePoint();

            Profiler.BeginSample("OnUpdateBattlePoint");
            BattlePresenter.OnUpdateBattlePoint(InGameScene.BattlePointModel);
            Profiler.EndSample();

            // デッキ更新
            UpdateDeck(removeUnitsResults.RemovedSpecialUnits);

            Profiler.BeginSample("OnUpdateDeck");
            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
            Profiler.EndSample();

            // 勝敗チェック
            UpdateBattleEnd();
        }

        void UpdateStageTime()
        {
            Profiler.BeginSample("UpdateStageTime");
            var stageTimeTickCount = TickCount.One;
#if GLOW_INGAME_DEBUG
            stageTimeTickCount = InGameScene.Debug.StageTimeSpeed;
#endif
            var stageTimeModel = StageTimeUpdateProcess.UpdateStageTime(
                InGameScene.StageTimeModel,
                stageTimeTickCount);

            InGameScene.StageTimeModel = stageTimeModel;
#if GLOW_INGAME_DEBUG
            DebugReportRepository.PushTickCount(InGameScene.StageTimeModel.CurrentTickCount.Value);
#endif

            // View側への制限時間の更新
            if (InGameScene.StageTimeModel.HasTimeLimit)
            {
                BattlePresenter.OnUpdateTimeLimit(InGameScene.StageTimeModel);
            }
            Profiler.EndSample();
        }

        void UpdateBossAppearancePause()
        {
            Profiler.BeginSample("UpdateBossAppearancePause");
            var updatedBossAppearancePause = BossAppearancePauseUpdateProcess.Update(
                InGameScene.BossAppearancePause,
                _tickCountUnit);

            if (!InGameScene.BossAppearancePause.IsEmpty() && updatedBossAppearancePause.IsEmpty())
            {
                BattlePresenter.OnBossAppearancePauseEnded();
            }

            InGameScene.BossAppearancePause = updatedBossAppearancePause;
            Profiler.EndSample();
        }

        void UpdateBossSummonQueue()
        {
            Profiler.BeginSample("UpdateBossSummonQueue");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var result = BossSummonQueueUpdateProcess.UpdateBossSummonQueue(
                InGameScene.CharacterUnits,
                InGameScene.BossSummonQueue,
                InGameScene.BossAppearancePause,
                InGameScene.Attacks,
                InGameScene.KomaDictionary,
                _tickCountUnit);

            InGameScene.CharacterUnits = result.UpdatedUnits;
            InGameScene.BossSummonQueue = result.UpdatedBossSummonQueue;
            InGameScene.BossAppearancePause = result.UpdatedBossAppearancePause;
            InGameScene.Attacks = result.UpdatedAttacks;

            if (result.SummonedBoss.IsEmpty())
            {
                Profiler.EndSample();
                return;
            }

            BattlePresenter.OnSummonCharacter(result.SummonedBoss);
            AddDiscoveredEnemyId(result.SummonedBoss);

            // 消去された攻撃をBattlePresenterに伝える
            foreach (var attack in result.RemovedAttacks)
            {
                BattlePresenter.OnEndAttack(attack);
            }
            BattlePresenter.OnUpdateAttacks(InGameScene.Attacks);
            Profiler.EndSample();
        }

        void UpdateUnitSummonQueue()
        {
            Profiler.BeginSample("UpdateUnitSummonQueue");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var result = UnitSummonQueueUpdateProcess.UpdateUnitSummonQueue(
                    InGameScene.UnitSummonQueue,
                    InGameScene.BossSummonQueue,
                    InGameScene.DeckUnitSummonQueue,
                    InGameScene.CharacterUnits,
                    InGameScene.KomaDictionary,
                    _tickCountUnit);

            InGameScene.UnitSummonQueue = result.UpdatedUnitSummonQueueModel;
            InGameScene.DeckUnitSummonQueue = result.UpdatedDeckUnitSummonQueueModel;
            InGameScene.CharacterUnits = result.UpdatedUnitModelList;

            if (result.SummonUnitModelList.Count == 0)
            {
                Profiler.EndSample();
                return;
            }

            foreach (var summonedCharacterUnit in result.SummonUnitModelList)
            {
                BattlePresenter.OnSummonCharacter(summonedCharacterUnit);
                AddDiscoveredEnemyId(summonedCharacterUnit);
            }
            Profiler.EndSample();
        }

        void UpdateSpecialUnitSummonQueue()
        {
            Profiler.BeginSample("UpdateSpecialUnitSummonQueue");
            var result = SpecialUnitSummonQueueUpdateProcess.UpdateSummonQueue(
                InGameScene.SpecialUnits,
                InGameScene.UsedSpecialUnitIdsBeforeNextRush,
                InGameScene.SpecialUnitSummonQueue,
                InGameScene.KomaDictionary,
                InGameScene.MstPage);

            InGameScene.SpecialUnitSummonQueue = result.UpdatedSummonQueueModel;
            InGameScene.SpecialUnits = result.UpdatedSpecialUnits;
            InGameScene.UsedSpecialUnitIdsBeforeNextRush = result.UpdatedUsedSpecialUnitIdsBeforeNextRush;

            if (result.SummonedSpecialUnits.Count <= 0)
            {
                Profiler.EndSample();
                return;
            }

            foreach (var summonSpecialUnit in result.SummonedSpecialUnits)
            {
                // 必殺技の効果範囲を取得
                var attackElement = summonSpecialUnit.SpecialAttack.MainAttackElement;
                var coordinateRange = AttackRangeConverter.ToFieldCoordAttackRange(
                    summonSpecialUnit.BattleSide,
                    summonSpecialUnit.Pos,
                    attackElement.AttackRange,
                    InGameScene.MstPage,
                    CoordinateConverter);

                BattlePresenter.OnSummonSpecialUnit(summonSpecialUnit, coordinateRange);
            }
            Profiler.EndSample();
        }

        void UpdateTransformGimmickObjectToEnemyQueue()
        {
            Profiler.BeginSample("UpdateTransformGimmickObjectToEnemyQueue");
            var result = GimmickObjectToEnemyTransformationUpdateProcess.UpdateTransformation(
                InGameScene.GimmickObjects,
                InGameScene.GimmickObjectToEnemyTransformationModels,
                InGameScene.UnitSummonQueue,
                InGameScene.BossSummonQueue,
                _tickCountUnit);

            InGameScene.GimmickObjects = result.UpdatedGimmickObjectModels;
            InGameScene.GimmickObjectToEnemyTransformationModels = result.UpdatedGimmickObjectToEnemyTransformationModels;
            InGameScene.UnitSummonQueue = result.UpdatedUnitSummonQueue;
            InGameScene.BossSummonQueue = result.UpdatedBossSummonQueue;

            foreach (var gimmickObjectModel in result.TransformationStartedGimmickObjectModels)
            {
                BattlePresenter.OnGimmickObjectTransformationStarted(gimmickObjectModel);
            }
            Profiler.EndSample();
        }

        void UpdateAutoPlayer()
        {
            Profiler.BeginSample("UpdateAutoPlayer");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var result = AutoPlayerUpdateProcess.UpdateAutoPlayer(
                InGameScene.DeckUnits,
                InGameScene.PvpOpponentDeckUnits,
                InGameScene.CharacterUnits,
                InGameScene.SpecialUnits,
                InGameScene.DeadUnits,
                InGameScene.GimmickObjectToEnemyTransformationModels,
                InGameScene.DefeatEnemyCount,
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.UnitSummonQueue,
                InGameScene.BossSummonQueue,
                InGameScene.DeckUnitSummonQueue,
                InGameScene.SpecialUnitSummonQueue,
                InGameScene.SpecialUnitSummonInfoModel,
                InGameScene.KomaDictionary,
                InGameScene.MstPage,
                InGameScene.BattlePointModel,
                InGameScene.PvpOpponentBattlePointModel,
                InGameScene.StageTimeModel,
                InGameScene.PvpOpponentRushModel,
                _tickCountUnit);

            foreach (var characterUnit in result.SummonedUnitList)
            {
                BattlePresenter.OnSummonCharacter(characterUnit);
                AddDiscoveredEnemyId(characterUnit);
            }

            InGameScene.CharacterUnits = result.UpdatedUnitList;
            InGameScene.BossSummonQueue = result.UpdatedBossSummonQueue;
            InGameScene.UnitSummonQueue = result.UpdatedUnitSummonQueue;
            InGameScene.DeckUnitSummonQueue = result.UpdatedDeckUnitSummonQueue;
            InGameScene.SpecialUnitSummonQueue = result.UpdatedSpecialUnitSummonQueue;
            InGameScene.GimmickObjectToEnemyTransformationModels = result.UpdatedGimmickObjectToEnemyTransformationModels;
            InGameScene.DeckUnits = result.UpdatedDeckUnitList;
            InGameScene.PvpOpponentDeckUnits = result.UpdatedOpponentDeckUnitList;
            InGameScene.BattlePointModel = result.UpdatedBattlePointModel;
            InGameScene.PvpOpponentBattlePointModel = result.UpdatedPvpOpponentBattlePointModel;
            InGameScene.PvpOpponentRushModel = result.UpdatePvpOpponentRushModel;
        }

        void UpdateStateEffects()
        {
            Profiler.BeginSample("UpdateStateEffects");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            InGameScene.CharacterUnits = StateEffectUpdateProcess.UpdateStateEffects(
                    InGameScene.CharacterUnits, _tickCountUnit);
            Profiler.EndSample();
        }

        void UpdateSpecialUnits()
        {
            Profiler.BeginSample("UpdateSpecialUnits");
            var result = SpecialUnitUpdateProcess.UpdateSpecialUnits(
                InGameScene.SpecialUnits,
                InGameScene.Attacks,
                InGameScene.MstPage,
                _tickCountUnit);

            InGameScene.SpecialUnits = result.UpdatedSpecialUnits;
            InGameScene.Attacks = result.UpdatedAttacks;
            Profiler.EndSample();
        }

        UpdateCharacterUnitsResultModel UpdateCharacterUnits()
        {
            Profiler.BeginSample("UpdateCharacterUnits");
            var result = CharacterUnitUpdateProcess.UpdateCharacterUnits(
                InGameScene.CharacterUnits,
                InGameScene.DeadUnits,
                InGameScene.DefeatEnemyCount,
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.DefenseTarget,
                InGameScene.KomaDictionary,
                InGameScene.Attacks,
                InGameScene.MstPage,
                InGameScene.BossAppearancePause,
                InGameScene.StageTimeModel,
                _tickCountUnit);

            InGameScene.CharacterUnits = result.UpdatedUnits;
            InGameScene.Attacks = result.UpdatedAttacks;

            foreach (var attack in result.GeneratedAttacks)
            {
                BattlePresenter.OnAppearAttack(attack);
            }

            var returnValue = new UpdateCharacterUnitsResultModel(result.BlockedUnits);
            Profiler.EndSample();
            return returnValue;
        }

        ExecuteAttacksResultModel ExecuteAttacks()
        {
            Profiler.BeginSample("ExecuteAttacks");
            var result = AttackProcess.UpdateAttacks(
                InGameScene.Attacks,
                InGameScene.CharacterUnits,
                InGameScene.SpecialUnits,
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.DefenseTarget,
                InGameScene.BossAppearancePause,
                InGameScene.StageTimeModel,
                InGameScene.RushModel,
                InGameScene.PvpOpponentRushModel,
                InGameScene.PlacedItems,
                InGameScene.DeckUnits,
                InGameScene.PvpOpponentDeckUnits,
                _tickCountUnit,
                InGameScene.KomaDictionary,
                InGameScene.MstPage);

            InGameScene.CharacterUnits = result.UpdatedUnits;
            InGameScene.PlayerOutpost = result.UpdatedPlayerOutpost;
            InGameScene.EnemyOutpost = result.UpdatedEnemyOutpost;
            InGameScene.DefenseTarget = result.UpdatedDefenseTarget;
            InGameScene.Attacks = result.UpdatedAttacks;
            InGameScene.RushModel = result.UpdatedRushModel;
            InGameScene.PvpOpponentRushModel = result.UpdatedPvpOpponentRushModel;
            InGameScene.PlacedItems = result.UpdatedPlacedItems;
            InGameScene.DeckUnits = result.UpdatedPlayerDeckUnits;
            InGameScene.PvpOpponentDeckUnits = result.UpdatedPvpOpponentDeckUnits;

            var maxEnemyDamage = result.AppliedAttackResults
                .Where(appliedAttackResult => appliedAttackResult.TargetBattleSide == BattleSide.Enemy)
                .Select(appliedAttackResult => appliedAttackResult.Damage)
                .DefaultIfEmpty(Damage.Zero)
                .Max();

            InGameLogger.UpdateMaxDamage(maxEnemyDamage);

            BattlePresenter.OnUpdateAttacks(result.UpdatedAttacks);

            foreach (var attack in result.RemovedAttacks)
            {
                BattlePresenter.OnEndAttack(attack);
            }
            foreach (var survivedUnit in result.SurvivedByGutsUnits.Distinct())
            {
                BattlePresenter.OnSurvivedByGuts(survivedUnit);
            }

            foreach (var stateEffect in result.AppliedDeckStateEffectResultModels)
            {
                BattlePresenter.OnDeckStateEffect(stateEffect);
            }

            var returnValue = new ExecuteAttacksResultModel(
                result.AppliedAttackResults,
                result.BlockedUnits);
            Profiler.EndSample();
            return returnValue;
        }

        void UpdateScore(
            IReadOnlyList<CharacterUnitModel> deadUnits,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            Profiler.BeginSample("UpdateScore");
            var updatedProcessModel = ScoreUpdateProcess.UpdateScore(
                InGameScene.ScoreModel,
                InGameScene.ScoreCalculateModel,
                InGameScene.CharacterUnits,
                deadUnits,
                InGameScene.EnemyOutpost,
                appliedAttackResults);

            InGameScene.ScoreModel = updatedProcessModel.UpdatedScoreModel;

            BattlePresenter.OnUpdateScore(
                updatedProcessModel.UpdatedScoreModel.TotalScore,
                updatedProcessModel.AddedScoreModels,
                InGameScene.ScoreModel.IsScoreEffectVisible);
            Profiler.EndSample();
        }

        /// <summary>
        /// キャラを削除して、そのリストを返す
        /// </summary>
        RemoveUnitsResultModel RemoveUnits()
        {
            Profiler.BeginSample("RemoveUnits");
            var result = UnitRemovingProcess.Update(InGameScene.CharacterUnits, InGameScene.SpecialUnits);

            InGameScene.CharacterUnits = result.UpdatedUnits;
            InGameScene.DeadUnits = result.DeadUnits;
            InGameScene.DefeatEnemyCount += result.DefeatEnemyCount;
            InGameScene.SpecialUnits = result.UpdatedSpecialUnits;

            // 倒した敵のカウントを保存する
            InGameLogger.AddDefeatEnemyCount(result.DefeatEnemyCount, result.DefeatBossEnemyCount);

            // 敵のIDごとに保存
            InGameLogger.ConcatDefeatEnemyCountDictionary(result.DefeatEnemyCountDictionary);

            if (DefeatEnemyCount.Zero < result.DefeatEnemyCount)
            {
                BattlePresenter.OnDefeatEnemy(InGameScene.DefeatEnemyCount, GetDefeatCountsByEnemy());
            }

            var returnValue = new RemoveUnitsResultModel(result.DeadUnits, result.RemovedSpecialUnits);
            Profiler.EndSample();
            return returnValue;
        }

        void RemoveGimmickObject()
        {
            Profiler.BeginSample("RemoveGimmickObject");
            var result = GimmickObjectRemovingProcess.Update(InGameScene.GimmickObjects);

            InGameScene.GimmickObjects = result.UpdatedGimmickObjects;

            BattlePresenter.OnGimmickObjectsRemoved(result.RemovedGimmickObjects);
            Profiler.EndSample();
        }

        void UpdateDeck(IReadOnlyList<SpecialUnitModel> removeSpecialUnits)
        {
            Profiler.BeginSample("UpdateDeck");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var deckUpdateProcessResult = DeckUpdateProcess.Update(
                InGameScene.DeckUnits,
                InGameScene.PvpOpponentDeckUnits,
                InGameScene.CharacterUnits,
                InGameScene.DeadUnits,
                InGameScene.SpecialUnits,
                removeSpecialUnits,
                _tickCountUnit);

            InGameScene.DeckUnits = deckUpdateProcessResult.UpdatedDeckUnits;
            InGameScene.PvpOpponentDeckUnits = deckUpdateProcessResult.UpdatedPvpOpponentDeckUnits;
            Profiler.EndSample();
        }

        void UpdateBattlePoint()
        {
            Profiler.BeginSample("UpdateBattlePoint");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var prevBp = InGameScene.BattlePointModel.CurrentBattlePoint;

            var battlePointUpdateProcessResult = BattlePointUpdateProcess.UpdateBattlePoint(
                InGameScene.BattlePointModel,
                InGameScene.PvpOpponentBattlePointModel,
                InGameScene.OutpostEnhancement,
                InGameScene.PvpOpponentOutpostEnhancement,
                InGameScene.DeadUnits,
                _tickCountUnit);

            InGameScene.BattlePointModel = battlePointUpdateProcessResult.UpdatedBpModel;
            InGameScene.PvpOpponentBattlePointModel = battlePointUpdateProcessResult.UpdatedOpponentBpModel;
            Profiler.EndSample();
        }

        void UpdateRush()
        {
            Profiler.BeginSample("UpdateRush");
            if (!InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero())
            {
                Profiler.EndSample();
                return;
            }

            var rushModel = InGameScene.RushModel;
            var pvpOpponentRushModel = InGameScene.PvpOpponentRushModel;

            var rushUpdateProcessResult = RushUpdateProcess.UpdateRush(
                rushModel,
                pvpOpponentRushModel,
                _tickCountUnit,
                InGameScene.CharacterUnits,
                InGameScene.UsedSpecialUnitIdsBeforeNextRush,
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.MstPage,
                InGameScene.Attacks);
            
            // 演出再生
            if (rushModel.ExecuteRushFlag)
            {
                BattlePresenter.OnExecuteRush(
                    rushModel,
                    rushUpdateProcessResult.CalculatedPlayerRushDamage,
                    rushUpdateProcessResult.RushEvaluationType,
                    BattleSide.Player,
                    InGameScene.CharacterUnits,
                    InGameScene.DeckUnits);
            }
            else if (pvpOpponentRushModel.ExecuteRushFlag)
            {
                BattlePresenter.OnExecuteRush(
                    pvpOpponentRushModel,
                    rushUpdateProcessResult.CalculatedPlayerRushDamage,
                    rushUpdateProcessResult.RushEvaluationType,
                    BattleSide.Enemy,
                    InGameScene.CharacterUnits,
                    InGameScene.PvpOpponentDeckUnits);
            }

            InGameScene.RushModel = rushUpdateProcessResult.RushModel;
            InGameScene.PvpOpponentRushModel = rushUpdateProcessResult.PvpOpponentRushModel;
            InGameScene.Attacks = rushUpdateProcessResult.UpdatedAttacks;
            InGameScene.UsedSpecialUnitIdsBeforeNextRush = rushUpdateProcessResult.UpdatedUsedSpecialUnitIdsBeforeNextRush;

            BattlePresenter.OnUpdateRushGauge(rushUpdateProcessResult.RushModel);

            if (!rushUpdateProcessResult.PvpOpponentRushModel.IsEmpty())
            {
                BattlePresenter.OnUpdatePvpOpponentRushGauge(rushUpdateProcessResult.PvpOpponentRushModel);
            }

            Profiler.EndSample();
        }

        void UpdateUnitAbilities()
        {
            Profiler.BeginSample("UpdateUnitAbilities");
            // ボス登場時のプレイヤーキャラノックバックでコマを跨いで特性が発動する場合があるので
            // ボス登場時の一時停止中でも処理する

            var updatedCharacterUnits = UnitAbilityProcess.UpdateUnitAbility(InGameScene.CharacterUnits);

            InGameScene.CharacterUnits = updatedCharacterUnits;
            Profiler.EndSample();
        }

        UpdateKomaEffectsResultModel UpdateKomaEffects()
        {
            Profiler.BeginSample("UpdateKomaEffects");
            // ボス登場時のプレイヤーキャラノックバックでコマを跨いでコマ効果が発動する場合があるので
            // ボス登場時の一時停止中でも処理する
            var isBossAppearancePause = !InGameScene.BossAppearancePause.RemainingPauseFrames.IsZero();

            var result = KomaEffectProcess.UpdateKomaEffects(
                InGameScene.CharacterUnits,
                InGameScene.KomaDictionary,
                InGameScene.MstPage,
                _tickCountUnit,
                isBossAppearancePause);

            InGameScene.KomaDictionary = result.UpdatedKomaDictionary;
            InGameScene.CharacterUnits = result.AffectedCharacterUnits;

            BattlePresenter.OnUpdateKomas(InGameScene.KomaDictionary);
            var returnValue = new UpdateKomaEffectsResultModel(result.BlockedUnits);
            Profiler.EndSample();
            return returnValue;
        }

        void UpdateMangaAnimations()
        {
            Profiler.BeginSample("UpdateMangaAnimations");
            var result = MangaAnimationUpdateProcess.UpdateMangaAnimations(
                InGameScene.MangaAnimations,
                InGameScene.CharacterUnits,
                _tickCountUnit);

            if (result.StartingMangaAnimations.Count > 0)
            {
                BattlePresenter.OnMangaAnimationStart(result.StartingMangaAnimations);
            }

            InGameScene.MangaAnimations = result.UpdatedMangaAnimationModels;
            Profiler.EndSample();
        }

        void UpdateBattleEnd()
        {
            Profiler.BeginSample("UpdateBattleEnd");
            var battleEndCheckProcessResult = BattleEndCheckProcess.UpdateBattleEnd(
                InGameScene.BattleEndModel,
                InGameScene.StageTimeModel,
                InGameScene.PlayerOutpost.Hp,
                InGameScene.EnemyOutpost.Hp,
                InGameScene.DefenseTarget.Hp,
                InGameScene.DeadUnits,
                InGameScene.DefeatEnemyCount,
                GetDefeatCountsByEnemy(),
                InGameScene.IsBattleGiveUp);

            if (!battleEndCheckProcessResult.IsBattleOver)
            {
                Profiler.EndSample();
                return;
            }

            var battleEndConditionModel = battleEndCheckProcessResult.MetBattleEndCondition;
            switch (battleEndConditionModel.StageEndType)
            {
                case StageEndType.Victory:
                    BattlePresenter.OnVictory(battleEndConditionModel.StageEndConditionType);
                    InGameScene.IsBattleOver = BattleOverFlag.True;
                    break;
                case StageEndType.Defeat:
                    var canContinue = !InGameScene.IsContinued && !InGameScene.IsNoContinue;
                    if (canContinue)
                    {
                        InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.True;
                        BattlePresenter.OnDefeatWithContinue(battleEndConditionModel.StageEndConditionType);
                    }
                    else
                    {
                        BattlePresenter.OnDefeatCannotContinue(battleEndConditionModel.StageEndConditionType);
                    }
                    InGameScene.IsBattleOver = BattleOverFlag.True;
                    break;
                case StageEndType.Finish:
                    BattlePresenter.OnFinish(battleEndConditionModel.StageEndConditionType);
                    InGameScene.IsBattleOver = BattleOverFlag.True;
                    break;
            }
            Profiler.EndSample();
        }

        void TransformUnits()
        {
            Profiler.BeginSample("TransformUnits");
            var updatedUnitSummonQueue = UnitTransformationProcess.Update(
                InGameScene.CharacterUnits,
                InGameScene.UnitSummonQueue);

            InGameScene.UnitSummonQueue = updatedUnitSummonQueue;
            Profiler.EndSample();
        }

        void Speak()
        {
            Profiler.BeginSample("Speak");
            var speechBalloons = SpeechBalloonProcess.Update(InGameScene.CharacterUnits);

            foreach (var speechBalloon in speechBalloons)
            {
                BattlePresenter.OnSpeak(speechBalloon);
            }
            Profiler.EndSample();
        }

        void UpdateEffectBlocked(UpdateCharacterUnitsResultModel updateCharacterUnitsResultModel,
            UpdateKomaEffectsResultModel updateKomaEffectsResultModel,
            ExecuteAttacksResultModel executeAttacksResultModel)
        {
            Profiler.BeginSample("UpdateEffectBlocked");
            var blockedUnits = new List<FieldObjectId>();
            blockedUnits.AddRange(updateCharacterUnitsResultModel.BlockedUnits);
            blockedUnits.AddRange(updateKomaEffectsResultModel.BlockedUnits);
            blockedUnits.AddRange(executeAttacksResultModel.BlockedUnits);

            foreach (var unitId in blockedUnits.Distinct())
            {
                // ブロックエフェクトを再生
                BattlePresenter.OnEffectBlocked(unitId);
            }
            Profiler.EndSample();
        }

        void UpdatePlacedItems()
        {
            Profiler.BeginSample("UpdatePlacedItems");
            var updatedPlacedItemsResult = UpdatePlacedItemsProcess.Update(
                InGameScene.PlacedItems);
            InGameScene.PlacedItems = updatedPlacedItemsResult.UpdatedItems;

            BattlePresenter.OnPlaceItems(updatedPlacedItemsResult.NewPlacedItems);
            BattlePresenter.OnRemoveConsumedItems(updatedPlacedItemsResult.ConsumedItems);

            Profiler.EndSample();
        }

        void AddDiscoveredEnemyId(CharacterUnitModel summonedUnit)
        {
            if (summonedUnit.BattleSide == BattleSide.Enemy)
            {
                InGameLogger.AddDiscoverEnemyId(summonedUnit.CharacterId);
            }
        }

        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> GetDefeatCountsByEnemy()
        {
            return InGameLogger.GetDefeatEnemyCountDictionary();
        }
    }
}
