using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class RushUpdateProcess : IRushUpdateProcess
    {
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IBuffStatePercentageConverter BuffStatePercentageConverter { get; }
        [Inject] IAttackModelFactory AttackModelFactory { get; }
        [Inject] IInGameUnitStatusCalculator InGameUnitStatusCalculator { get; }
        
        // 総攻撃評価基準(フィールド上のユニット数＋事前に召喚していたスペシャルユニット数)
        const int EvaluationThresholdGood = 3;
        const int EvaluationThresholdGreat = 6;
        const int EvaluationThresholdExcellent = 9;
        const int EvaluationThresholdFantastic = 10;

        public RushUpdateProcessResult UpdateRush(
            RushModel rushModel,
            RushModel pvpOpponentRushModel,
            TickCount tickCount,
            IReadOnlyList<CharacterUnitModel> fieldUnits,
            IReadOnlyList<MasterDataId> usedSpecialUnitIdsBeforeNextRush,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            MstPageModel mstPage,
            IReadOnlyList<IAttackModel> attacks)
        {
            var updatedRushModel = rushModel;
            var updatedPvpOpponentRushModel = pvpOpponentRushModel;
            var updatedAttacks = attacks;

            var updatedRushResult = UpdateRush(
                updatedRushModel,
                tickCount,
                fieldUnits,
                playerOutpost,
                mstPage,
                updatedAttacks,
                BattleSide.Player,
                rushModel.ExecuteRushFlag);
            updatedRushModel = updatedRushResult.updatedRushModel;
            updatedAttacks = updatedRushResult.updatedAttacks;

            var updatedOpponentRushResult = UpdateRush(
                updatedPvpOpponentRushModel,
                tickCount,
                fieldUnits,
                enemyOutpost,
                mstPage,
                updatedAttacks,
                BattleSide.Enemy,
                !rushModel.ExecuteRushFlag); // ←プレイヤーが総攻撃を発動した場合、相手は総攻撃を発動しない
            updatedPvpOpponentRushModel = updatedOpponentRushResult.updatedRushModel;
            updatedAttacks = updatedOpponentRushResult.updatedAttacks;

            AttackPower calculatedPlayerRushDamage;
            if (rushModel.ExecuteRushFlag)
            {
                // プレイヤー側の総攻撃が発動した場合、プレイヤー側の総攻撃ダメージを設定
                calculatedPlayerRushDamage = updatedRushResult.calculatedPlayerRushDamage;
            }
            else if (pvpOpponentRushModel.ExecuteRushFlag)
            {
                // 相手側の総攻撃が発動した場合、相手側の総攻撃ダメージを設定
                calculatedPlayerRushDamage = updatedOpponentRushResult.calculatedPlayerRushDamage;
            }
            else
            {
                // どちらの総攻撃も発動しなかった場合、総攻撃ダメージは0
                calculatedPlayerRushDamage = AttackPower.Zero;
            }
            
            // 総攻撃が発動した場合、フィールド上に存在するキャラと事前に召喚していたスペシャルユニットIDの一覧をカウント
            var evaluationType = EvaluateRush(
                fieldUnits,
                usedSpecialUnitIdsBeforeNextRush,
                rushModel.ExecuteRushFlag);
            
            // 総攻撃が発動した場合、次の総攻撃までに使用されたスペシャルユニットIDの一覧をリセット
            var updatedUsedSpecialUnitIdsBeforeNextRush =  rushModel.ExecuteRushFlag ?
                new List<MasterDataId>() :
                usedSpecialUnitIdsBeforeNextRush;

            return new RushUpdateProcessResult(
                updatedRushModel,
                updatedPvpOpponentRushModel,
                updatedAttacks,
                calculatedPlayerRushDamage,
                evaluationType,
                updatedUsedSpecialUnitIdsBeforeNextRush);
        }

        (RushModel updatedRushModel, IReadOnlyList<IAttackModel> updatedAttacks, AttackPower calculatedPlayerRushDamage) UpdateRush(
            RushModel rushModel,
            TickCount tickCount,
            IReadOnlyList<CharacterUnitModel> fieldUnits,
            OutpostModel outpost,
            MstPageModel mstPage,
            IReadOnlyList<IAttackModel> attacks,
            BattleSide battleSide,
            ExecuteRushFlag canExecuteRush)
        {
            if (rushModel.IsEmpty()) return (rushModel, attacks, AttackPower.Zero);

            var remainingChargeTime = rushModel.RemainingChargeTime - tickCount;
            var chargeCount = rushModel.ChargeCount;
            var fieldPlayerUnits = fieldUnits
                .Where(unit => unit.BattleSide == battleSide)
                .ToList();
            var updatedAttacks = new List<IAttackModel>(attacks.ToList());

            // チャージ数管理
            // 最大チャージ数までチャージできる
            if (remainingChargeTime <= TickCount.Zero && chargeCount < rushModel.MaxChargeCount)
            {
                remainingChargeTime = rushModel.ChargeTime;
                chargeCount += 1;
            }

            // 総攻撃 発動時
            if (rushModel.ExecuteRushFlag && canExecuteRush)
            {
                // BaseAttackPowerを計算
                // フィールド上のユニット取得
                var totalFieldUnitAttackPower = AttackPower.Zero;
                foreach (var fieldUnit in fieldPlayerUnits)
                {
                    var buffs = fieldUnit.StateEffects;
                    totalFieldUnitAttackPower +=
                        InGameUnitStatusCalculator.CalculateBuffAttackPower(fieldUnit.AttackPower, buffs);
                }

                // チャージボーナスの取得
                var totalBonus = PercentageM.Hundred + rushModel.SpecialUnitBonus + rushModel.GetCurrentChargeBonus();

                // ノックバックタイプの取得
                var knockBackType = rushModel.GetCurrentKnockBackType();

                // 総攻撃威力上昇効果ボーナス
                var stateEffectBonus = PercentageM.Hundred + rushModel.PowerUpStateEffectBonus.ToPercentageM();

                // 計算式
                // バフ・デバフ込みの攻撃力合計 × ボーナス合計 × 総攻撃の威力アップ効果合計 × 総攻撃ダメージ係数
                var calculatedAttackPower =
                    totalFieldUnitAttackPower * totalBonus * stateEffectBonus * rushModel.Coefficient;

                // 最大ダメージ上限
                calculatedAttackPower = AttackPower.Min(calculatedAttackPower, rushModel.MaxRushAttackPower);

                var rushAttackElement = rushModel.AttackData.AttackElements.First();
                var updateRushAttackElement = rushAttackElement with
                {
                    AttackHitData = CreateAttackHitData(knockBackType)
                };

                // AttackModelを作成
                var (attack, _) = AttackModelFactory.Create(
                    outpost.Id,
                    MasterDataId.Empty,
                    outpost.StateEffectSourceId,
                    battleSide,
                    CharacterUnitRoleType.None,
                    CharacterColor.None,
                    OutpostCoordV2.Empty,
                    calculatedAttackPower,
                    HealPower.Empty,
                    CharacterColorAdvantageAttackBonus.Default,
                    rushModel.AttackData.BaseData,
                    updateRushAttackElement,
                    new List<IStateEffectModel>(), // 各キャラのバフデバフは別途計算して合計するのでEmptyで返す
                    mstPage,
                    CoordinateConverter,
                    BuffStatePercentageConverter);

                var updateResetRushModel = rushModel with
                {
                    RemainingChargeTime = rushModel.ChargeTime,
                    ChargeCount = RushChargeCount.Zero,
                    ExecuteRushFlag = ExecuteRushFlag.False,
                    CanExecuteRushFlag = CanExecuteRushFlag.False,
                    PowerUpStateEffectBonus = RushPowerUpStateEffectBonus.Zero
                };

                updatedAttacks.Add(attack);

                return (updateResetRushModel, updatedAttacks, calculatedAttackPower);
            }

            // 総攻撃が実行可能かどうか判断する
            // 実行可能条件：フィールド上に自身のキャラが1対以上存在する、チャージ数が1段階以上ある
            var canExecute = fieldPlayerUnits.Any() && chargeCount > RushChargeCount.Zero;
            var canExecuteRushFlag = canExecute ? CanExecuteRushFlag.True : CanExecuteRushFlag.False;

            var updateRushModel = rushModel with
            {
                RemainingChargeTime = remainingChargeTime,
                ChargeCount = chargeCount,
                CanExecuteRushFlag = canExecuteRushFlag
            };

            return (updateRushModel, updatedAttacks, AttackPower.Zero);
        }

        AttackHitData CreateAttackHitData(AttackHitType hitType)
        {
            AttackHitData attackHitData;
            switch (hitType)
            {
                case AttackHitType.Normal: attackHitData = AttackHitData.Normal; break;
                case AttackHitType.KnockBack1: attackHitData = AttackHitData.KnockBack1; break;
                case AttackHitType.KnockBack2: attackHitData = AttackHitData.KnockBack2; break;
                case AttackHitType.KnockBack3: attackHitData = AttackHitData.KnockBack3; break;
                case AttackHitType.ForcedKnockBack1: attackHitData = AttackHitData.ForcedKnockBack1; break;
                case AttackHitType.ForcedKnockBack2: attackHitData = AttackHitData.ForcedKnockBack2; break;
                case AttackHitType.ForcedKnockBack3: attackHitData = AttackHitData.ForcedKnockBack3; break;
                case AttackHitType.ForcedKnockBack5: attackHitData = AttackHitData.ForcedKnockBack5; break;
                default: attackHitData = AttackHitData.ForcedKnockBack2; break;
            }

            attackHitData = attackHitData with
            {
                AttackHitBattleEffectId = new AttackHitBattleEffectId(BattleEffectId.RushHit)
            };

            return attackHitData;
        }

        RushEvaluationType EvaluateRush(
            IReadOnlyList<CharacterUnitModel> fieldUnits,
            IReadOnlyList<MasterDataId> usedSpecialUnitIdsBeforeNextRush,
            ExecuteRushFlag executeRushFlag)
        {
            if (!executeRushFlag) return RushEvaluationType.None;
            
            var allSummonedUnitIds = new List<MasterDataId>();
            var fieldPlayerUnits = fieldUnits
                .Where(unit => unit.BattleSide == BattleSide.Player)
                .Select(unit => unit.CharacterId)
                .ToList();
            
            allSummonedUnitIds.AddRange(fieldPlayerUnits);
            allSummonedUnitIds.AddRange(usedSpecialUnitIdsBeforeNextRush);

            return allSummonedUnitIds.Count switch
            {
                <= EvaluationThresholdGood => RushEvaluationType.Good,
                <= EvaluationThresholdGreat => RushEvaluationType.Great,
                <= EvaluationThresholdExcellent => RushEvaluationType.Excellent,
                EvaluationThresholdFantastic => RushEvaluationType.Fantastic,
                _ => RushEvaluationType.None
            };
        }
    }
}
