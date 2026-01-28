using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class CharacterUnitUpdateProcess : ICharacterUnitUpdateProcess
    {

        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IAttackModelFactory AttackModelFactory { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }
        [Inject(Id = Battle.AutoPlayer.AutoPlayer.EnemyAutoPlayerBindId)] IAutoPlayer EnemyAutoPlayer { get; }
        [Inject] IStateEffectChecker StateEffectChecker { get; }
        [Inject] IBuffStatePercentageConverter BuffStatePercentageConverter { get; }
        [Inject] INearestTargetFinder NearestTargetFinder { get; }

        public CharacterUnitUpdateProcessResult UpdateCharacterUnits(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            DefeatEnemyCount totalDeadEnemyCount,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            DefenseTargetModel defenseTargetModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<IAttackModel> attacks,
            MstPageModel mstPage,
            BossAppearancePauseModel bossAppearancePause,
            StageTimeModel stageTime,
            TickCount tickCount)
        {
            var characterUnitCount = characterUnits.Count;
            var updatedCharacterUnits = new List<CharacterUnitModel>(characterUnitCount);
            var generatedAttacks = new List<IAttackModel>();
            var blockedUnits = new List<FieldObjectId>();

            // 攻撃対象候補 - 事前容量確保
            var attackTargetCandidates = new List<IAttackTargetModel>(characterUnitCount + 3);
            attackTargetCandidates.AddRange(characterUnits);
            attackTargetCandidates.Add(playerOutpost);
            attackTargetCandidates.Add(enemyOutpost);
            attackTargetCandidates.Add(defenseTargetModel);

            // ボス登場判定用HashSetで高速化
            var appearedBossSet = bossAppearancePause.AppearedBossList.Count > 0
                ? new HashSet<FieldObjectId>(bossAppearancePause.AppearedBossList)
                : null;
            
            var isBossAppearancePause = !bossAppearancePause.RemainingPauseFrames.IsZero();

            // StateEffect処理用の一時リスト（再利用）
            var tempAttacks = new List<IAttackModel>(4);
            var tempStateEffects = new List<IStateEffectModel>(8);

            // キャラを位置でソートしておく
            var sortedPlayerAttackTargetCandidates = GetSortedAttackTargets(attackTargetCandidates, BattleSide.Player);
            var sortedEnemyAttackTargetCandidates = GetSortedAttackTargets(attackTargetCandidates, BattleSide.Enemy);
            
            // キャラの更新
            for (int i = 0; i < characterUnitCount; i++)
            {
                var unit = characterUnits[i];

                // ボス登場時一時停止中でもプレイヤーキャラと登場ボスは動かす
                if (isBossAppearancePause && unit.BattleSide != BattleSide.Player && 
                    (appearedBossSet == null || !appearedBossSet.Contains(unit.Id)))
                {
                    updatedCharacterUnits.Add(unit);
                    continue;
                }

                // StateEffect処理を統合
                tempAttacks.Clear();
                tempStateEffects.Clear();
                bool isBlocked = false;

                ProcessStateEffects(unit, mstPage, tempAttacks, tempStateEffects, ref isBlocked);

                generatedAttacks.AddRange(tempAttacks);

                // 参照を切るため新しいListを作成(そのまま代入だと次のユニットの状態に上書きされてしまう)
                unit = unit with { StateEffects = new List<IStateEffectModel>(tempStateEffects) };

                if (isBlocked)
                {
                    blockedUnits.Add(unit.Id);
                }

                // キャラAction
                var context = new CharacterUnitActionContext(
                    unit,
                    characterUnits,
                    deadUnits,
                    totalDeadEnemyCount,
                    attackTargetCandidates,
                    sortedPlayerAttackTargetCandidates,
                    sortedEnemyAttackTargetCandidates,
                    playerOutpost,
                    enemyOutpost,
                    defenseTargetModel,
                    komaDictionary,
                    CoordinateConverter,
                    AttackModelFactory,
                    StateEffectModelFactory,
                    mstPage,
                    stageTime,
                    tickCount,
                    EnemyAutoPlayer.CurrentAutoPlayerSequenceGroupModel,
                    StateEffectChecker,
                    BuffStatePercentageConverter,
                    NearestTargetFinder);

                var (updatedUnit, attacksByUnitAction) = unit.Action.Update(context);

                // モデルの更新
                updatedCharacterUnits.Add(updatedUnit);
                attackTargetCandidates[i] = updatedUnit;

                // 発生した攻撃をリストに追加
                if (!isBossAppearancePause || (appearedBossSet != null && appearedBossSet.Contains(unit.Id)))
                {
                    generatedAttacks.AddRange(attacksByUnitAction);
                }
            }

            // 最終的な攻撃リスト作成 - 容量を事前計算
            var updatedAttacks = new List<IAttackModel>(attacks.Count + generatedAttacks.Count);
            updatedAttacks.AddRange(attacks);
            updatedAttacks.AddRange(generatedAttacks);

            return new CharacterUnitUpdateProcessResult(
                updatedCharacterUnits,
                generatedAttacks,
                updatedAttacks,
                blockedUnits);
        }

        void ProcessStateEffects(
            CharacterUnitModel unit, 
            MstPageModel mstPage,
            List<IAttackModel> resultAttacks,
            List<IStateEffectModel> resultStateEffects,
            ref bool isBlocked)
        {
            resultStateEffects.AddRange(unit.StateEffects);
            
            for (int i = 0; i < resultStateEffects.Count; i++)
            {
                var stateEffect = resultStateEffects[i];
                
                switch (stateEffect.Type)
                {
                    case StateEffectType.Poison:
                    case StateEffectType.Burn:
                        ProcessDamageStateEffect(unit, mstPage, stateEffect, resultAttacks);
                        break;

                    case StateEffectType.SlipDamage:
                        // SlipDamage処理では内部でStateEffectsが更新される
                        var slipDamageBlocked = ProcessSlipDamageEffect(
                            unit, 
                            mstPage, 
                            stateEffect, 
                            resultAttacks, 
                            resultStateEffects);
                        
                        if (slipDamageBlocked) isBlocked = true;
                        break;

                    case StateEffectType.RegenerationByFixed:
                    case StateEffectType.RegenerationByMaxHpPercentage:
                        ProcessRegenerationEffect(unit, mstPage, stateEffect, i, resultAttacks, resultStateEffects);
                        break;
                }
            }
        }

        void ProcessDamageStateEffect(
            CharacterUnitModel unit,
            MstPageModel mstPage,
            IStateEffectModel stateEffect,
            List<IAttackModel> resultAttacks)
        {
            var attackData = stateEffect.GenerateAttack();
            if (attackData.IsEmpty()) return;

            CreateAttacksFromData(unit, mstPage, attackData, resultAttacks);
        }

        bool ProcessSlipDamageEffect(
            CharacterUnitModel unit,
            MstPageModel mstPage,
            IStateEffectModel stateEffect,
            List<IAttackModel> resultAttacks,
            List<IStateEffectModel> resultStateEffects)
        {
            var attackData = stateEffect.GenerateAttack();
            if (attackData.IsEmpty())
            {
                return false;
            }

            var tempAttacks = new List<IAttackModel>(attackData.AttackElements.Count);
            CreateAttacksFromData(unit, mstPage, attackData, tempAttacks);

            bool isBlocked = false;

            foreach (var attack in tempAttacks)
            {
                var slipDamageKomaBlockResult = StateEffectChecker.CheckAndReduceCount(
                    StateEffectType.SlipDamageKomaBlock,
                    resultStateEffects);
                
                resultStateEffects.Clear();
                resultStateEffects.AddRange(slipDamageKomaBlockResult.UpdatedStateEffects);
                
                if (slipDamageKomaBlockResult.IsEffectActivated)
                {
                    isBlocked = true;
                }
                else
                {
                    resultAttacks.Add(attack);
                }
            }

            return isBlocked;
        }

        void ProcessRegenerationEffect(
            CharacterUnitModel unit,
            MstPageModel mstPage,
            IStateEffectModel stateEffect,
            int stateEffectIndex,
            List<IAttackModel> resultAttacks,
            List<IStateEffectModel> resultStateEffects)
        {
            var attackData = stateEffect.GenerateAttack();
            if (attackData.IsEmpty())
            {
                return;
            }

            CreateAttacksFromData(unit, mstPage, attackData, resultAttacks);

            var updatedRegenerationStateEffectModel = (RegenerationStateEffectModel)stateEffect;
            if (!updatedRegenerationStateEffectModel.IsGeneratedFirstAttack)
            {
                updatedRegenerationStateEffectModel = updatedRegenerationStateEffectModel with
                {
                    IsGeneratedFirstAttack = GeneratedFirstAttackFlag.True,
                };
            }

            resultStateEffects[stateEffectIndex] = updatedRegenerationStateEffectModel;
        }

        void CreateAttacksFromData(
            CharacterUnitModel unit,
            MstPageModel mstPage,
            AttackData attackData,
            List<IAttackModel> resultAttacks)
        {
            foreach (var attackElement in attackData.AttackElements)
            {
                var (attack, _) = AttackModelFactory.Create(
                    unit.Id,
                    unit.CharacterId,
                    unit.StateEffectSourceId,
                    unit.BattleSide,
                    CharacterUnitRoleType.None,
                    CharacterColor.None,
                    unit.Pos,
                    unit.AttackPower,
                    unit.HealPower,
                    CharacterColorAdvantageAttackBonus.Empty,
                    attackData.BaseData,
                    attackElement,
                    Array.Empty<IStateEffectModel>(),
                    mstPage,
                    CoordinateConverter,
                    BuffStatePercentageConverter);

                resultAttacks.Add(attack);
            }
        }

        IReadOnlyList<IAttackTargetModel> GetSortedAttackTargets(
            IReadOnlyList<IAttackTargetModel> attackTargets,
            BattleSide battleSide)
        {
            return attackTargets
                .Where(unit => unit.BattleSide == battleSide)
                .OrderByDescending(unit => unit.Pos.X)
                .ThenBy(candidate => candidate.AttackTargetOrder)
                .ThenBy(candidate => candidate.PosUpdateStageTickCount)
                .ToList();
        }
    }
}
