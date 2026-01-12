using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    /// <summary> ロールがスペシャルのキャラ用の更新処理、召喚から一定時間経っていれば必殺技効果を発動する </summary>
    public class SpecialUnitUpdateProcess : ISpecialUnitUpdateProcess
    {
        [Inject] IAttackModelFactory AttackModelFactory { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IBuffStatePercentageConverter BuffStatePercentageConverter { get; }

        public SpecialUnitUpdateProcessResult UpdateSpecialUnits(
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<IAttackModel> attacks,
            MstPageModel mstPage,
            TickCount tickCount)
        {
            var updatedSpecialUnits = new List<SpecialUnitModel>(specialUnits);
            var generatedAttacks = new List<IAttackModel>();
            for (int i = 0; i < specialUnits.Count; i++)
            {
                var unit = specialUnits[i];
                var updatedRemainingTimeUntilSpecialAttackCharge = unit.RemainingTimeUntilSpecialAttackCharge;
                var updatedRemainingTimeUntilSpecialAttack = unit.RemainingTimeUntilSpecialAttack;
                var updatedSpecialAttackChargeFlag = SpecialUnitSpecialAttackChargeFlag.False;
                var updatedUseSpecialAttackFlag = SpecialUnitUseSpecialAttackFlag.False;
                var updatedUnitStateEffects = unit.StateEffects;

                if (!updatedRemainingTimeUntilSpecialAttackCharge.IsEmpty())
                {
                    // 必殺技チャージ開始タイミングになったら必殺技チャージ開始
                    updatedRemainingTimeUntilSpecialAttackCharge -= tickCount;
                    if (updatedRemainingTimeUntilSpecialAttackCharge <= TickCount.Zero)
                    {
                        updatedRemainingTimeUntilSpecialAttackCharge = TickCount.Empty;
                        updatedSpecialAttackChargeFlag = SpecialUnitSpecialAttackChargeFlag.True;
                    }
                }

                if (!updatedRemainingTimeUntilSpecialAttack.IsEmpty())
                {
                    // 必殺技発動タイミングになったら必殺技発動
                    updatedRemainingTimeUntilSpecialAttack -= tickCount;
                    if (updatedRemainingTimeUntilSpecialAttack <= TickCount.Zero)
                    {
                        updatedRemainingTimeUntilSpecialAttack = TickCount.Empty;
                        updatedUseSpecialAttackFlag = SpecialUnitUseSpecialAttackFlag.True;
                    }
                }

                var updatedRemainingTimeEndSpecialAttack = unit.RemainingTimeEndSpecialAttack;
                if (!updatedRemainingTimeEndSpecialAttack.IsEmpty())
                {
                    // 必殺技演出終了後の効果発生タイミングになったら効果を発生
                    updatedRemainingTimeEndSpecialAttack -= tickCount;
                    if (updatedRemainingTimeEndSpecialAttack <= TickCount.Zero)
                    {
                        foreach (var attackElement in unit.SpecialAttack.AttackElements)
                        {
                            (var generatedAttack, var updatedEffects) = AttackModelFactory.Create(
                                unit.Id,
                                unit.CharacterId,
                                unit.StateEffectSourceId,
                                unit.BattleSide,
                                CharacterUnitRoleType.Special,
                                unit.Color,
                                unit.Pos,
                                unit.AttackPower,
                                unit.HealPower,
                                unit.ColorAdvantageAttackBonus,
                                unit.SpecialAttack.BaseData,
                                attackElement,
                                updatedUnitStateEffects,
                                mstPage,
                                CoordinateConverter,
                                BuffStatePercentageConverter);
                            generatedAttacks.Add(generatedAttack);
                            updatedUnitStateEffects = updatedEffects;
                        }

                        updatedRemainingTimeEndSpecialAttack = TickCount.Empty;
                    }
                }

                var updatedRemainingLeavingTime = unit.RemainingLeavingTime;
                if (!updatedRemainingLeavingTime.IsEmpty())
                {
                    // 退去演出終了の時間になったら除外対象のユニットに
                    updatedRemainingLeavingTime -= tickCount;
                    if (updatedRemainingLeavingTime <= TickCount.Zero)
                    {
                        updatedRemainingLeavingTime = TickCount.Empty;
                    }
                }

                // ユニット更新
                var updatedUnit = unit with
                {
                    RemainingTimeUntilSpecialAttackCharge = updatedRemainingTimeUntilSpecialAttackCharge,
                    RemainingTimeUntilSpecialAttack = updatedRemainingTimeUntilSpecialAttack,
                    RemainingTimeEndSpecialAttack = updatedRemainingTimeEndSpecialAttack,
                    RemainingLeavingTime = updatedRemainingLeavingTime,
                    SpecialUnitSpecialAttackChargeFlag = updatedSpecialAttackChargeFlag,
                    SpecialUnitUseSpecialAttackFlag = updatedUseSpecialAttackFlag,
                    StateEffects = updatedUnitStateEffects
                };
                updatedSpecialUnits[i] = updatedUnit;
            }

            var updatedAttacks = attacks.Concat(generatedAttacks).ToList();

            return new SpecialUnitUpdateProcessResult(
                updatedSpecialUnits,
                generatedAttacks,
                updatedAttacks);
        }
    }
}
