using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.Translators;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;
#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.Repositories.Debug;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.ValueObjects;
#endif

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class AttackProcess : IAttackProcess
    {
        record FeedbackApplyingResult(
            IReadOnlyList<CharacterUnitModel> UpdatedUnits,
            IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults);

        record HitInfo(CharacterUnitRoleType AttackerRoleType, CharacterColor AttackerColor, AttackHitData HitData)
        {
            public static HitInfo Empty { get; } = new(
                CharacterUnitRoleType.None,
                CharacterColor.None,
                AttackHitData.Empty);
        }

        record HitSelectionResult(
            HitInfo SelectedHitInfo,
            List<HitInfo> NotYetEvaluatedHitInfos, // 選択されたHitDataより優先度が低く、まだ評価されてないHitData。※ 選択されたHitDataと同じ優先度のものも含む場合もある
            IReadOnlyList<IStateEffectModel> UpdatedStateEffects,
            bool IsHitActionBlocked)
        {
            public static HitSelectionResult Empty { get; } = new(
                HitInfo.Empty,
                new List<HitInfo>(),
                Array.Empty<IStateEffectModel>(),
                false);

            public bool IsEmpty() => ReferenceEquals(this, Empty);
        }

        record GetNewActionResult(
            ICharacterUnitAction CharacterUnitAction,
            IReadOnlyList<IStateEffectModel> UpdatedStateEffects,
            bool IsHitActionBlocked);

        [Inject] IRandomProvider RandomProvider { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IHPCalculator HPCalculator { get; }
        [Inject] IAttackFeedbackHPCalculator AttackFeedbackHPCalculator { get; }
        [Inject] IAttackResultModelFactory AttackResultModelFactory { get; }
        [Inject] IAttackFeedbackModelFactory AttackFeedbackModelFactory { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }
        [Inject] ICharacterUnitActionFactory CharacterUnitActionFactory { get; }
        [Inject(Id = Battle.AutoPlayer.AutoPlayer.EnemyAutoPlayerBindId)] IAutoPlayer EnemyAutoPlayer { get; }
        [Inject] IStateEffectChecker StateEffectChecker { get; }
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }

#if GLOW_INGAME_DEBUG
        [Inject] IInGameDebugReportRepository DebugReportRepository { get; }
        [Inject] IInGameScene InGameScene { get; }
#endif

        public AttackProcessResult UpdateAttacks(
            IReadOnlyList<IAttackModel> attacks,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            DefenseTargetModel defenseTargetModel,
            BossAppearancePauseModel bossAppearancePause,
            StageTimeModel stageTime,
            RushModel rushModel,
            RushModel pvpOpponentRushModel,
            IReadOnlyList<PlacedItemModel> placedItems,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            TickCount tickCount,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage)
        {
            var attackTargetCandidates = new List<IAttackTargetModel>(characterUnits);
            attackTargetCandidates.Add(playerOutpost);
            attackTargetCandidates.Add(enemyOutpost);
            attackTargetCandidates.Add(defenseTargetModel);

            var survivedByGutsUnits = new List<FieldObjectId>();

            (IReadOnlyList<IAttackModel> updatedAttacks, IReadOnlyList<IAttackResultModel> attackResults, IReadOnlyList<PlacedItemModel> updatedItems) =
                ExecuteAttacks(
                    attackTargetCandidates, 
                    attacks, 
                    bossAppearancePause, 
                    placedItems, 
                    playerDeckUnits, 
                    pvpOpponentDeckUnits, 
                    tickCount,
                    komaDictionary,
                    mstPage);

            var hitAttackResults = attackResults
                .OfType<HitAttackResultModel>()
                .ToList();

            var applyingUnitResult = ApplyAttacks(characterUnits, hitAttackResults);
            var updatedUnits = applyingUnitResult.UpdatedUnits;

            var applyingPlayerOutpostResult = ApplyAttacks(playerOutpost, hitAttackResults, updatedUnits);
            var applyingEnemyOutpostResult = ApplyAttacks(enemyOutpost, hitAttackResults, updatedUnits);

            var applyingDefenseTargetResult = ApplyAttacks(defenseTargetModel, hitAttackResults);

            var applyingRushModelResult = ApplyAttacks(
                specialUnits,
                rushModel,
                pvpOpponentRushModel,
                hitAttackResults);

            // 根性で生き延びたUnit
            survivedByGutsUnits.AddRange(applyingUnitResult.SurvivedByGutsUnits);

            // 攻撃結果に対するフィードバック（現状、キャラへの攻撃のみに対応）
            var feedbacks = AttackFeedbackModelFactory.Create(applyingUnitResult.AppliedAttackResults);
            var feedbackApplyingResult = ApplyAttackFeedbacks(updatedUnits, feedbacks);
            updatedUnits = feedbackApplyingResult.UpdatedUnits;

            var appliedAttackResults = applyingUnitResult.AppliedAttackResults
                .Concat(applyingPlayerOutpostResult.AppliedAttackResults)
                .Concat(applyingEnemyOutpostResult.AppliedAttackResults)
                .Concat(applyingDefenseTargetResult.AppliedAttackResults)
                .Concat(feedbackApplyingResult.AppliedAttackResults)
                .ToList();

            // Actionの更新
            var updateUnitActionResult = UpdateUnitAction(
                updatedUnits,
                appliedAttackResults,
                survivedByGutsUnits,
                stageTime);
            updatedUnits = updateUnitActionResult.updatedUnits;

            // AttackModelの更新
            var removedAttacks = updatedAttacks
                .Where(attack => attack.IsEnd)
                .ToList();

            updatedAttacks = updatedAttacks
                .Where(attack => !attack.IsEnd)
                .ToList();

            // Deckに対して発生した効果と発動者
            var appliedDeckStateEffectResultModels =
                new List<AppliedDeckStateEffectResultModel>(applyingRushModelResult.DeckStateEffectResultModels);

            // TODO 将来拡張する場合はメソッドの多重定義でなく、引数/戻り値の汎用化が良さそう(あらゆる情報を各効果側に渡して効果側で取捨選択する形)
            // 即時効果処理（必殺・召喚クールタイム変動など）
            var immediateEffectResult = ApplyImmediateEffects(updatedUnits, playerDeckUnits, pvpOpponentDeckUnits, attackResults);
            appliedDeckStateEffectResultModels.AddRange(immediateEffectResult.appliedDeckStateEffectResultModels);


            // オブジェクト配置
            var placedItemAttackResults = attackResults
                .OfType<PlacedItemAttackResultModel>()
                .ToList();
            var placeItemResult = ApplyPlaceItem(updatedItems, placedItemAttackResults);

            // 無効化発生したユニット
            var allBlockedUnits = new List<FieldObjectId>();
            allBlockedUnits.AddRange(applyingUnitResult.BlockedUnits);
            allBlockedUnits.AddRange(updateUnitActionResult.blockedUnits);

            return new AttackProcessResult(
                removedAttacks,
                updatedAttacks,
                updatedUnits,
                applyingPlayerOutpostResult.Outpost,
                applyingEnemyOutpostResult.Outpost,
                applyingDefenseTargetResult.DefenseTarget,
                applyingRushModelResult.UpdatedRushModel,
                applyingRushModelResult.UpdatedPvpOpponentRushModel,
                appliedAttackResults,
                allBlockedUnits,
                survivedByGutsUnits,
                appliedDeckStateEffectResultModels,
                placeItemResult.AppliedPlaceItems,
                immediateEffectResult.updatedPlayerDeckUnits,
                immediateEffectResult.updatedPvpOpponentDeckUnits);
        }

        public (IReadOnlyList<IAttackModel>, IReadOnlyList<IAttackResultModel>, IReadOnlyList<PlacedItemModel>) ExecuteAttacks(
            IReadOnlyList<IAttackTargetModel> attackTargetCandidates,
            IReadOnlyList<IAttackModel> attacks,
            BossAppearancePauseModel bossAppearancePause,
            IReadOnlyList<PlacedItemModel> placedItems,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            TickCount tickCount,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage)
        {
            var updatedAttacks = new List<IAttackModel>();
            var allAttackResults = new List<IAttackResultModel>();
            var updatedPlacedItems = new List<PlacedItemModel>();
            
            var alreadyPlacedItemKomaIdSet = placedItems
                .Where(item => item.PlaceItemState == PlaceItemState.EffectAvailable)
                .Select(item => item.KomaId)
                .ToHashSet();

            var context = new AttackModelContext(
                attackTargetCandidates,
                RandomProvider,
                CoordinateConverter,
                AttackResultModelFactory,
                tickCount,
                playerDeckUnits,
                pvpOpponentDeckUnits,
                komaDictionary,
                mstPage,
                placedItems,
                alreadyPlacedItemKomaIdSet);

            foreach (var attack in attacks)
            {
                // ボス登場時一時停止中は、登場ボスによる攻撃以外の更新をしない
                if (!bossAppearancePause.RemainingPauseFrames.IsZero()
                    && !bossAppearancePause.AppearedBossList.Contains(attack.AttackerId)) continue;

                var (updatedAttack, attackResults)  = attack.UpdateAttackModel(context);

                updatedAttacks.Add(updatedAttack);
                allAttackResults.AddRange(attackResults);
                
                // 既に配置されているコマのIDを追加
                var placedItemAttackResults = attackResults
                    .OfType<PlacedItemAttackResultModel>()
                    .ToList();

                if (placedItemAttackResults.IsEmpty()) continue;
                
                foreach (var placedItemAttackResult in placedItemAttackResults)
                {
                    alreadyPlacedItemKomaIdSet.Add(placedItemAttackResult.KomaId);
                }
                
                context = context with
                {
                    AlreadyPlacedItemKomaIdSet = alreadyPlacedItemKomaIdSet
                };
            }
            
            // ボス登場時一時停止中は、登場ボスによる攻撃以外の更新をしない
            if (!bossAppearancePause.RemainingPauseFrames.IsZero())
            {
                return (updatedAttacks, allAttackResults, placedItems);
            }
            
            foreach (var item in placedItems)
            {
                var (updatedItem, attackResults) = item.ExecuteAttack(context);

                var damageAttackResults = attackResults
                    .Where(result => result is HitAttackResultModel)
                    .ToList();
                allAttackResults.AddRange(damageAttackResults);
                updatedPlacedItems.Add(updatedItem);
            }

            return (updatedAttacks, allAttackResults, updatedPlacedItems);
        }

        public AttackProcessApplyingUnitResult ApplyAttacks(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<HitAttackResultModel> attackResults)
        {
            var updatedUnits = new List<CharacterUnitModel>();
            var allAppliedAttackResults = new List<AppliedAttackResultModel>();
            var survivedByGutsUnits = new List<FieldObjectId>();
            var blockedUnits = new List<FieldObjectId>();

            foreach (var unit in characterUnits)
            {
#if GLOW_INGAME_DEBUG
                var isDebugInvalidateUnitDamage = unit.BattleSide == BattleSide.Player
                    ? InGameScene.Debug.IsPlayerUnitDamageInvalidation
                    : InGameScene.Debug.IsEnemyUnitDamageInvalidation;
#else
                var isDebugInvalidateUnitDamage = DamageInvalidationFlag.False;
#endif

                if (!attackResults.Any(result => result.TargetId == unit.Id))
                {
                    updatedUnits.Add(unit);
                    continue;
                }

                var hpCalculatorResult = HPCalculator.CalculateHp(
                    attackResults,
                    unit.Id,
                    unit.Color,
                    unit.ColorAdvantageDefenseBonus,
                    unit.Hp,
                    unit.MaxHp,
                    unit.StateEffects,
                    unit.Action.IsDamageInvalidation || isDebugInvalidateUnitDamage,
                    unit.Action.IsHealInvalidation,
                    unit.IsUndead());

                var updatedEffects = hpCalculatorResult.UpdatedStateEffects;
                bool isStateEffectBlocked = false;

                if (!unit.Action.IsAttackStateEffectInvalidation)
                {
                    (updatedEffects, isStateEffectBlocked) = GetNewEffects(
                        unit.Id,
                        unit.Action.IsDamageInvalidation,
                        updatedEffects,
                        attackResults);
                }

                if (isStateEffectBlocked)
                {
                    blockedUnits.Add(unit.Id);
                }

                var newUnit = unit with
                {
                    Hp = hpCalculatorResult.HP,
                    StateEffects = updatedEffects,
                };

                updatedUnits.Add(newUnit);

                var appliedAttackResults = hpCalculatorResult.Details
                    .Select(detail => AppliedAttackResultModelTranslator.Translate(detail, unit.BattleSide))
                    .ToList();

                allAppliedAttackResults.AddRange(appliedAttackResults);

                // AttackDamageType.NoneのAppliedAttackResultModelを追加
                var appliedAttackResultsForNoneDamageType = attackResults
                    .Where(result => result.AttackDamageType == AttackDamageType.None)
                    .Where(result => result.TargetId == unit.Id)
                    .Select(result => AppliedAttackResultModelTranslator.TranslateForNoneDamageType(
                        result,
                        newUnit.Hp,
                        unit.BattleSide))
                    .ToList();

                allAppliedAttackResults.AddRange(appliedAttackResultsForNoneDamageType);

                if (hpCalculatorResult.IsSurvivedByGuts)
                {
                    survivedByGutsUnits.Add(unit.Id);
                }

#if GLOW_INGAME_DEBUG
                foreach (var result in appliedAttackResults)
                {
                    DebugReportRepository.PushDamageReport(new DebugInGameLogDamageModel(
                        unit.BattleSide,
                        unit.AssetKey.ToDamageDebugLogTargetName(),
                        result.AttackHitData.HitType,
                        result.AttackDamageType,
                        result.Damage,
                        result.Heal,
                        result.BeforeHp,
                        result.AfterHp));
                }
                foreach (var resultForNoneDamageType in appliedAttackResultsForNoneDamageType)
                {
                    DebugReportRepository.PushDamageReport(new DebugInGameLogDamageModel(
                        unit.BattleSide,
                        unit.AssetKey.ToDamageDebugLogTargetName(),
                        resultForNoneDamageType.AttackHitData.HitType,
                        resultForNoneDamageType.AttackDamageType,
                        resultForNoneDamageType.Damage,
                        resultForNoneDamageType.Heal,
                        resultForNoneDamageType.BeforeHp,
                        resultForNoneDamageType.AfterHp));
                }
#endif
            }

            return new AttackProcessApplyingUnitResult(updatedUnits, allAppliedAttackResults, survivedByGutsUnits, blockedUnits);
        }

        public AttackProcessApplyingOutpostResult ApplyAttacks(
            OutpostModel outpost,
            IReadOnlyList<HitAttackResultModel> attackResults,
            IReadOnlyList<CharacterUnitModel> characterUnits)
        {
            if (!attackResults.Any(result => result.TargetId == outpost.Id))
            {
                return new AttackProcessApplyingOutpostResult(
                    outpost,
                    new List<AppliedAttackResultModel>());
            }

            // ダメージをトリガーとする敵召喚キャラが存在する状態の場合は不死扱いにする(オーバーキル防止)
            var isUndead = outpost.BattleSide == BattleSide.Enemy && EnemyAutoPlayer.RemainsSummonUnitByOutpostDamage()
                ? UndeadFlag.True
                : UndeadFlag.False;

            // 自陣側ゲートのユニットでボスなどゲートダメージ無効化フラグを持つユニットがいれば
            var isOutpostDamageInvalidation = characterUnits.Any(unit =>
                outpost.BattleSide == unit.BattleSide &&
                unit.IsOutpostDamageInvalidation);

            var hpResult = HPCalculator.CalculateHp(
                attackResults,
                outpost.Id,
                CharacterColor.None,
                CharacterColorAdvantageDefenseBonus.Default,
                outpost.Hp,
                outpost.MaxHp,
                Array.Empty<IStateEffectModel>(),
                new DamageInvalidationFlag(isOutpostDamageInvalidation),
                HealInvalidationFlag.False,
                isUndead);

            // クエストタイプによりダメージ無効のゲートであれば常にHPを最大の状態にする(強化クエストや降臨クエストは破壊されない)
            var hp = hpResult.HP;
            if (outpost.DamageInvalidationFlag.IsDamageInvalidation())
            {
                hp = outpost.MaxHp;
            }

            var newOutpost = outpost with { Hp = hp };

            var appliedAttackResults = hpResult.Details
                .Select(detail => AppliedAttackResultModelTranslator.Translate(detail, outpost.BattleSide))
                .ToList();

            // AttackDamageType.NoneのAppliedAttackResultModelを追加
            var appliedAttackResultsForNoneDamageType = attackResults
                .Where(result => result.AttackDamageType == AttackDamageType.None)
                .Where(result => result.TargetId == outpost.Id)
                .Select(result => AppliedAttackResultModelTranslator.TranslateForNoneDamageType(
                    result,
                    newOutpost.Hp,
                    newOutpost.BattleSide))
                .ToList();

            appliedAttackResults.AddRange(appliedAttackResultsForNoneDamageType);

#if GLOW_INGAME_DEBUG
            foreach (var appliedAttackResult in appliedAttackResults)
            {
                DebugReportRepository.PushDamageReport(new DebugInGameLogDamageModel(
                    outpost.BattleSide,
                    new DamageDebugLogTargetName("Outpost"),
                    appliedAttackResult.AttackHitData.HitType,
                    appliedAttackResult.AttackDamageType,
                    appliedAttackResult.Damage,
                    appliedAttackResult.Heal,
                    appliedAttackResult.BeforeHp,
                    appliedAttackResult.AfterHp));
            }
#endif

            return new AttackProcessApplyingOutpostResult(newOutpost, appliedAttackResults);
        }

        public AttackProcessApplyingDefenseTargetResult ApplyAttacks(
            DefenseTargetModel defenseTarget,
            IReadOnlyList<HitAttackResultModel> attackResults)
        {
            if (attackResults.All(result => result.TargetId != defenseTarget.Id))
            {
                return new AttackProcessApplyingDefenseTargetResult(
                    defenseTarget,
                    new List<AppliedAttackResultModel>());
            }

            var hpResult = HPCalculator.CalculateHp(
                attackResults,
                defenseTarget.Id,
                CharacterColor.None,
                CharacterColorAdvantageDefenseBonus.Default,
                defenseTarget.Hp,
                defenseTarget.MaxHp,
                Array.Empty<IStateEffectModel>(),
                DamageInvalidationFlag.False,
                HealInvalidationFlag.False,
                UndeadFlag.False);

            var newDefenseTarget = defenseTarget with { Hp = hpResult.HP };

            var appliedAttackResults = hpResult.Details
                .Select(detail => AppliedAttackResultModelTranslator.Translate(detail, defenseTarget.BattleSide))
                .ToList();

            // AttackDamageType.NoneのAppliedAttackResultModelを追加
            var appliedAttackResultsForNoneDamageType = attackResults
                .Where(result => result.AttackDamageType == AttackDamageType.None)
                .Where(result => result.TargetId == defenseTarget.Id)
                .Select(result => AppliedAttackResultModelTranslator.TranslateForNoneDamageType(
                    result,
                    newDefenseTarget.Hp,
                    newDefenseTarget.BattleSide))
                .ToList();

            appliedAttackResults.AddRange(appliedAttackResultsForNoneDamageType);

#if GLOW_INGAME_DEBUG
            foreach (var appliedAttackResult in appliedAttackResults)
            {
                DebugReportRepository.PushDamageReport(new DebugInGameLogDamageModel(
                    defenseTarget.BattleSide,
                    new DamageDebugLogTargetName("DefenseTarget"),
                    appliedAttackResult.AttackHitData.HitType,
                    appliedAttackResult.AttackDamageType,
                    appliedAttackResult.Damage,
                    appliedAttackResult.Heal,
                    appliedAttackResult.BeforeHp,
                    appliedAttackResult.AfterHp));
            }
#endif

            return new AttackProcessApplyingDefenseTargetResult(newDefenseTarget, appliedAttackResults);
        }

        public AttackProcessApplyingRushResult ApplyAttacks(
            IReadOnlyList<SpecialUnitModel> specialUnits,
            RushModel rushModel,
            RushModel pvpOpponentRushModel,
            IReadOnlyList<HitAttackResultModel> attackResults)
        {
            var powerUpResults = attackResults
                .Where(attackResult => attackResult.TargetId.IsEmpty())
                .Where(attackResult => attackResult.StateEffect.Type == StateEffectType.RushAttackPowerUp)
                .ToList();

            if (powerUpResults.Count <= 0)
            {
                // 変更なし
                return new AttackProcessApplyingRushResult(
                    rushModel,
                    pvpOpponentRushModel,
                    new List<AppliedDeckStateEffectResultModel>());
            }

            // 総攻撃威力上昇ボーナスはそのまま加算していく
            var playerPowerUpPercentage = PercentageM.Zero;
            var pvpOpponentPowerUpPercentage = PercentageM.Zero;
            var playerPowerUpAttackerIds = new List<FieldObjectId>();
            var pvpOpponentPowerUpAttackerIds = new List<FieldObjectId>();
            foreach (var powerUpResult in powerUpResults)
            {
                var attackerSpUnit =
                    specialUnits.FirstOrDefault(spUnit => spUnit.Id == powerUpResult.AttackerId, SpecialUnitModel.Empty);
                if (attackerSpUnit.IsEmpty()) continue;

                // 同じ発動者は一つにまとめた上で表示用に最終的な総攻撃威力上昇値を返す
                // 複数発動者が居て同時に反映となっても表示反映タイミングは同じなため問題ない認識
                if (attackerSpUnit.BattleSide == BattleSide.Player)
                {
                    playerPowerUpPercentage += powerUpResult.StateEffect.Parameter.ToPercentageM();
                    if (!playerPowerUpAttackerIds.Contains(attackerSpUnit.Id))
                    {
                        playerPowerUpAttackerIds.Add(attackerSpUnit.Id);
                    }
                }
                else
                {
                    pvpOpponentPowerUpPercentage += powerUpResult.StateEffect.Parameter.ToPercentageM();
                    if (!pvpOpponentPowerUpAttackerIds.Contains(attackerSpUnit.Id))
                    {
                        pvpOpponentPowerUpAttackerIds.Add(attackerSpUnit.Id);
                    }
                }
            }

            var stateEffectAttackResultModelList = new List<AppliedDeckStateEffectResultModel>();
            var updatedRushModel = rushModel;
            if (!playerPowerUpPercentage.IsZero())
            {
                updatedRushModel = rushModel with
                {
                    PowerUpStateEffectBonus = rushModel.PowerUpStateEffectBonus + playerPowerUpPercentage
                };

                var stateEffectAttackResultModel = new AppliedDeckStateEffectResultModel(
                    BattleSide.Player,
                    playerPowerUpAttackerIds,
                    StateEffectType.RushAttackPowerUp,
                    updatedRushModel.PowerUpStateEffectBonus.ToPercentageM(),
                    MasterDataId.Empty);
                stateEffectAttackResultModelList.Add(stateEffectAttackResultModel);
            }

            var updatedPvpOpponentRushModel = pvpOpponentRushModel;
            if (!pvpOpponentPowerUpPercentage.IsZero())
            {
                updatedPvpOpponentRushModel = pvpOpponentRushModel with
                {
                    PowerUpStateEffectBonus = pvpOpponentRushModel.PowerUpStateEffectBonus + pvpOpponentPowerUpPercentage
                };

                var stateEffectAttackResultModel = new AppliedDeckStateEffectResultModel(
                    BattleSide.Enemy,
                    pvpOpponentPowerUpAttackerIds,
                    StateEffectType.RushAttackPowerUp,
                    updatedPvpOpponentRushModel.PowerUpStateEffectBonus.ToPercentageM(),
                    MasterDataId.Empty);
                stateEffectAttackResultModelList.Add(stateEffectAttackResultModel);
            }

            return new AttackProcessApplyingRushResult(
                updatedRushModel,
                updatedPvpOpponentRushModel,
                stateEffectAttackResultModelList);
        }

        FeedbackApplyingResult ApplyAttackFeedbacks(
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<AttackFeedbackModel> feedbacks)
        {
            var updatedUnits = units.ToList();
            var allAppliedAttackResults = new List<AppliedAttackResultModel>();

            var attackerIds = feedbacks
                .Select(feedback => feedback.AttackerId)
                .Distinct();

            foreach (var attackerId in attackerIds)
            {
                var unit = units.FirstOrDefault(unit => unit.Id == attackerId);
                if (unit == null) continue;

                var hpCalculatorResult = AttackFeedbackHPCalculator.CalculateHp(
                    feedbacks,
                    unit.Id,
                    unit.Hp,
                    unit.MaxHp,
                    unit.Action.IsHealInvalidation);

                var updatedUnit = unit with { Hp = hpCalculatorResult.HP };
                updatedUnits.Replace(unit, updatedUnit);

                var appliedAttackResults = hpCalculatorResult.Details
                    .Select(detail => AppliedAttackResultModelTranslator.Translate(detail, unit.BattleSide));

                allAppliedAttackResults.AddRange(appliedAttackResults);

                // AttackDamageType.NoneのAppliedAttackResultModelを追加
                var appliedAttackResultsForNoneDamageType = feedbacks
                    .Where(result => result.AttackDamageType == AttackDamageType.None)
                    .Where(result => result.AttackerId == unit.Id)
                    .Select(result => AppliedAttackResultModelTranslator.TranslateForNoneDamageType(
                        result,
                        updatedUnit.Hp,
                        updatedUnit.BattleSide))
                    .ToList();

                allAppliedAttackResults.AddRange(appliedAttackResultsForNoneDamageType);

#if GLOW_INGAME_DEBUG
                foreach (var appliedAttackResult in allAppliedAttackResults)
                {
                    DebugReportRepository.PushDamageReport(new DebugInGameLogDamageModel(
                        unit.BattleSide,
                        unit.AssetKey.ToDamageDebugLogTargetName(),
                        appliedAttackResult.AttackHitData.HitType,
                        appliedAttackResult.AttackDamageType,
                        appliedAttackResult.Damage,
                        appliedAttackResult.Heal,
                        appliedAttackResult.BeforeHp,
                        appliedAttackResult.AfterHp));
                }
#endif
            }

            return new FeedbackApplyingResult(updatedUnits, allAppliedAttackResults);
        }

        AttackProcessApplyingPlaceItemResult ApplyPlaceItem(
            IReadOnlyList<PlacedItemModel> placedItems,
            IReadOnlyList<PlacedItemAttackResultModel> placedItemAttackResults)
        {
            var newPlacedItems = new List<PlacedItemModel>();
            foreach (var attackResult in placedItemAttackResults)
            {
                newPlacedItems.Add(new PlacedItemModel(
                    FieldObjectIdProvider.GenerateNewId(),
                    attackResult.PlacedItemBattleSide,
                    attackResult.KomaId,
                    attackResult.Pos,
                    attackResult.PickUpAttackElement,
                    PlaceItemState.Placing));
            }

            var updatedPlacedItems = placedItems.Concat(newPlacedItems).ToList();
            return new AttackProcessApplyingPlaceItemResult(updatedPlacedItems);
        }

        (List<CharacterUnitModel> updatedUnits, IReadOnlyList<FieldObjectId> blockedUnits) UpdateUnitAction(
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels,
            IReadOnlyList<FieldObjectId> survivedByGutsUnits,
            StageTimeModel stageTime)
        {
            var updatedUnits = new List<CharacterUnitModel>();
            var blockedUnits = new List<FieldObjectId>();
            var groupedAppliedAttackResults = appliedAttackResultModels
                .GroupBy(result => result.TargetId)
                .ToDictionary(group => group.Key);

            foreach (var unit in units)
            {
                if (!groupedAppliedAttackResults.ContainsKey(unit.Id))
                {
                    updatedUnits.Add(unit);
                    continue;
                }

                var prevState = unit.Action.ActionState;
                var newActionResult = GetNewAction(
                    unit,
                    groupedAppliedAttackResults[unit.Id].ToList(),
                    survivedByGutsUnits,
                    stageTime);

                var updatedUnit = unit with
                {
                    Action = newActionResult.CharacterUnitAction,
                    PrevActionState = prevState,
                    StateEffects = newActionResult.UpdatedStateEffects,
                };

                updatedUnits.Add(updatedUnit);
                if (newActionResult.IsHitActionBlocked)
                {
                    blockedUnits.Add(unit.Id);
                }
            }

            return (updatedUnits, blockedUnits);
        }

        GetNewActionResult GetNewAction(
            CharacterUnitModel unit,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults,
            IReadOnlyList<FieldObjectId> survivedByGutsUnits,
            StageTimeModel stageTime)
        {
            var hitInfos = appliedAttackResults
                .Select(result => new HitInfo(result.AttackerRoleType, result.AttackerColor, result.AttackHitData))
                .ToList();

            // ダメージ蓄積によるノックバック
            if (IsAccumulatedDamageKnockBack(unit.DamageKnockBackCount, unit.MaxHp, unit.Hp, appliedAttackResults))
            {
                var hitInfo = new HitInfo(
                    CharacterUnitRoleType.None,
                    CharacterColor.None,
                    AttackHitData.AccumulatedDamageKnockBack);

                hitInfos.Add(hitInfo);
            }

            // 根性発動によるノックバック
            if (survivedByGutsUnits.Contains(unit.Id))
            {
                var hitInfo = new HitInfo(
                    CharacterUnitRoleType.None,
                    CharacterColor.None,
                    AttackHitData.ForcedKnockBack2);

                hitInfos.Add(hitInfo);
            }

            // HitTypeの優先度でソート
            var orderedHitInfos = hitInfos
                .OrderByDescending(info => info.HitData.HitType.GetActionPriority())
                .ToList();

            // 適用するHitDataを選択
            var hitSelectionResult = SelectHit(orderedHitInfos, unit, unit.StateEffects, stageTime);
            var selectedHitData = hitSelectionResult.SelectedHitInfo.HitData;

            var updatedStateEffects = hitSelectionResult.UpdatedStateEffects;
            var isHitActionBlocked = hitSelectionResult.IsHitActionBlocked;

            // ノックバックの場合は、ノックバック後の次のUnitActionを決めるHitDataを選択
            var hitDataForNextHitAction = AttackHitData.Empty;

            if (selectedHitData.HitType.IsKnockBack())
            {
                var hitInfosForNextHitAction = hitSelectionResult.NotYetEvaluatedHitInfos
                    .Where(info => info.HitData.HitType.CanReserveActionAfterKnockBack())
                    .ToList();

                var resultForNextHitAction = SelectHit(hitInfosForNextHitAction, unit, updatedStateEffects, stageTime);

                hitDataForNextHitAction = resultForNextHitAction.SelectedHitInfo.HitData;
                updatedStateEffects = resultForNextHitAction.UpdatedStateEffects;
                isHitActionBlocked |= resultForNextHitAction.IsHitActionBlocked;
            }

            var unitAction = CharacterUnitActionFactory.CreateAttackHitAction(
                selectedHitData,
                hitDataForNextHitAction,
                unit,
                stageTime);

            return new GetNewActionResult(unitAction, updatedStateEffects, isHitActionBlocked);
        }

        HitSelectionResult SelectHit(
            IReadOnlyList<HitInfo> orderedHitInfos,
            CharacterUnitModel unit,
            IReadOnlyList<IStateEffectModel> stateEffects,
            StageTimeModel stageTime)
        {
            HitSelectionResult result = HitSelectionResult.Empty;

            var updatedStateEffects = stateEffects;
            var hitActionBlocked = false;

            for (var i = 0; i < orderedHitInfos.Count; i++)
            {
                var hitInfo = orderedHitInfos[i];

                // 状態変化による無効化
                var stateEffectTypeThatBlockHitType = hitInfo.HitData.HitType.GetStateEffectTypeThatBlockMe();
                if (stateEffectTypeThatBlockHitType != StateEffectType.None)
                {
                    var context = new StateEffectAttackHitConditionContext(
                        hitInfo.AttackerColor,
                        hitInfo.AttackerRoleType);

                    var blockHitTypeResult = StateEffectChecker.CheckAndReduceCount(
                        stateEffectTypeThatBlockHitType,
                        updatedStateEffects,
                        context);

                    updatedStateEffects = blockHitTypeResult.UpdatedStateEffects;

                    if (blockHitTypeResult.IsEffectActivated)
                    {
                        hitActionBlocked = true;
                        continue;
                    }
                }

                var unitActionState = hitInfo.HitData.HitType.GetUnitActionState();

                if (unitActionState == UnitActionState.None || unit.Action.CanForceChangeTo(unitActionState))
                {
                    result = new HitSelectionResult(
                        hitInfo,
                        orderedHitInfos.Skip(i + 1).ToList(),
                        updatedStateEffects,
                        hitActionBlocked);
                    break;
                }
            }

            if (result.IsEmpty())
            {
                result = new HitSelectionResult(
                    HitInfo.Empty,
                    new List<HitInfo>(),
                    updatedStateEffects,
                    hitActionBlocked);
            }

            return result;
        }

        bool IsAccumulatedDamageKnockBack(
            KnockBackCount damageKnockBackCount,
            HP maxHp,
            HP newHp,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            if (damageKnockBackCount.IsZero()) return false;

            int knockBackHpThreshold = Mathf.CeilToInt(maxHp.Value / (float)(damageKnockBackCount.Value + 1));
            if (knockBackHpThreshold == 0) return false;

            var totalDamage = appliedAttackResults
                .Select(result => result.AppliedDamage)
                .Sum();

            var totalHeal = appliedAttackResults
                .Select(result => result.AppliedHeal)
                .Sum();

            var prevHp = newHp + totalDamage - totalHeal;

            int remainingKnockBackCount = Mathf.CeilToInt(prevHp.Value / (float)knockBackHpThreshold);
            int updatedRemainingKnockBackCount = Mathf.CeilToInt(newHp.Value / (float)knockBackHpThreshold);
            int knockBackCount = remainingKnockBackCount - updatedRemainingKnockBackCount;

            if (knockBackCount <= 0) return false;

            // ヒットした攻撃が全てダメージ蓄積ノックバック無効の場合はノックバックしない
            if (appliedAttackResults.All(result => !result.AttackHitData.IsAccumulatedDamageKnockBack)) return false;

            return true;
        }

        (IReadOnlyList<IStateEffectModel>, bool) GetNewEffects(
            FieldObjectId characterUnitId,
            bool isInvincible,
            IReadOnlyList<IStateEffectModel> currentEffects,
            IReadOnlyList<HitAttackResultModel> attackResults)
        {
            // 対象キャラへの攻撃結果だけを抽出(即時効果はStateEffect生成しないため除外)
            var attackResultsForTarget = attackResults
                .Where(result =>
                    !result.StateEffect.Type.IsImmediateEffect()
                    && result.TargetId == characterUnitId
                    && !result.StateEffect.IsEmpty())
                .ToList();

            // attackResultsForTargetに含まれる重複不可Statusを1つにFilterする
            var filterByHasNotMulti = new List<HitAttackResultModel>();
            foreach (var result in attackResultsForTarget)
            {
                if(filterByHasNotMulti.Any(f =>
                    f.StateEffect.Type.HasNotMulti() && f.StateEffect.Type == result.StateEffect.Type))
                    continue;

                filterByHasNotMulti.Add(result);
            }

            var filteredByBlock = new List<HitAttackResultModel>();
            var updatedEffects = currentEffects;
            var stateEffectBlocked = false;
            foreach (var result in filterByHasNotMulti)
            {
                // 無効化判定
                var blockType = result.StateEffect.Type.GetStateEffectTypeThatBlockMe();
                if (blockType != StateEffectType.None)
                {
                    var context = new StateEffectAttackHitConditionContext(
                        result.AttackerColor,
                        result.AttackerRoleType);

                    var blockResult = StateEffectChecker.CheckAndReduceCount(
                        blockType,
                        updatedEffects,
                        context,
                        result.StateEffectSourceId);

                    // CheckAndReduceCount()による更新結果を反映
                    updatedEffects = blockResult.UpdatedStateEffects;

                    if (blockResult.IsEffectActivated)
                    {
                        stateEffectBlocked = true;
                        continue;  // 無効化されたのでスキップ
                    }
                }

                filteredByBlock.Add(result);
            }

            // 新規付与分を作成(既に存在する重複不可状態変化、無敵状態で付与不可な状態変化は除外)
            var additionalEffectList = filteredByBlock
                .Where(result =>StateEffectChecker.ShouldAttachHasNotMultiState(result.StateEffect, updatedEffects))
                .Where(result => StateEffectChecker.ShouldAttachInInvincible(isInvincible,result.StateEffect))
                .Select(result => StateEffectModelFactory.Create(result.StateEffectSourceId, result.StateEffect, true))
                .ToList();

            // 既存(更新済み)と追加分を結合
            var updatedStateEffectList = updatedEffects.Concat(additionalEffectList).ToList();

            return (updatedStateEffectList, stateEffectBlocked);
        }

        (IReadOnlyList<DeckUnitModel> updatedPlayerDeckUnits,
            IReadOnlyList<DeckUnitModel> updatedPvpOpponentDeckUnits,
            IReadOnlyList<AppliedDeckStateEffectResultModel> appliedDeckStateEffectResultModels)
            ApplyImmediateEffects(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<IAttackResultModel> attackResults)
        {
            // TODO 将来拡張する場合はメソッドの多重定義でなく、引数/戻り値の汎用化が良さそう(あらゆる情報を各効果側に渡して効果側で取捨選択する形)
            var updatedPlayerDeckUnits = playerDeckUnits.ToList();
            var updatedPvpOpponentDeckUnits = pvpOpponentDeckUnits.ToList();
            var appliedDeckStateEffectResultModels = new List<AppliedDeckStateEffectResultModel>();

            foreach (var attackResult in attackResults)
            {
                if (attackResult.StateEffect.IsEmpty()) continue;

                // 即時効果以外はスキップ
                var stateEffectType = attackResult.StateEffect.Type;
                if (!stateEffectType.IsImmediateEffect()) continue;

                // 効果ごとに処理
                var handler = ImmediateEffectHandlerFactory.GetHandler(stateEffectType);
                if (handler != null)
                {
                    var (updatedPlayer, updatedPvpOpponent, applied) =
                        handler.Handle(attackResult, characterUnits, updatedPlayerDeckUnits, updatedPvpOpponentDeckUnits);
                    updatedPlayerDeckUnits = updatedPlayer.ToList();
                    updatedPvpOpponentDeckUnits = updatedPvpOpponent.ToList();
                    appliedDeckStateEffectResultModels.AddRange(applied);
                }
            }

            return (updatedPlayerDeckUnits, updatedPvpOpponentDeckUnits, appliedDeckStateEffectResultModels);
        }
    }
}
