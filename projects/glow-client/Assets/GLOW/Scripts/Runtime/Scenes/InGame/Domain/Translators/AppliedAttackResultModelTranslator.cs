using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Translators
{
    public static class AppliedAttackResultModelTranslator
    {
        public static AppliedAttackResultModel Translate(
            HPCalculatorResultDetailModel hpCalculatorResultDetailModel,
            BattleSide targetBattleSide)
        {
            var attackResult = hpCalculatorResultDetailModel.AttackResult;
            if (attackResult == null || attackResult.IsEmpty())
            {
                return AppliedAttackResultModel.Empty;
            }
            
            var hitData = attackResult.AttackHitData;
            if (hitData.HitType == AttackHitType.Drain && hpCalculatorResultDetailModel.AppliedDamage.IsZero())
            {
                hitData = AttackHitData.Normal;
            }

            return new AppliedAttackResultModel(
                attackResult.TargetId,
                targetBattleSide,
                attackResult.AttackerId,
                attackResult.AttackDamageType,
                hitData,
                attackResult.IsHitStop,
                hpCalculatorResultDetailModel.Damage,
                hpCalculatorResultDetailModel.Heal,
                hpCalculatorResultDetailModel.AppliedDamage,
                hpCalculatorResultDetailModel.AppliedHeal,
                hpCalculatorResultDetailModel.BeforeHp,
                hpCalculatorResultDetailModel.AfterHp,
                attackResult.AttackerRoleType,
                attackResult.AttackerColor,
                hpCalculatorResultDetailModel.IsKillerAttack,
                hpCalculatorResultDetailModel.IsAdvantageUnitColor);
        }

        public static AppliedAttackResultModel Translate(
            AttackFeedbackHPCalculatorResultDetailModel attackFeedbackHPCalculatorResultDetailModel,
            BattleSide targetBattleSide)
        {
            var attackFeedback = attackFeedbackHPCalculatorResultDetailModel.AttackFeedback;

            return new AppliedAttackResultModel(
                attackFeedback.AttackerId,      // Attackerが攻撃フェードバックの適用対象になる
                targetBattleSide,
                FieldObjectId.Empty,
                attackFeedback.AttackDamageType,
                attackFeedback.AttackHitData,
                AttackHitStopFlag.False,
                Damage.Zero,
                attackFeedbackHPCalculatorResultDetailModel.Heal,
                Damage.Zero,
                attackFeedbackHPCalculatorResultDetailModel.AppliedHeal,
                attackFeedbackHPCalculatorResultDetailModel.BeforeHp,
                attackFeedbackHPCalculatorResultDetailModel.AfterHp,
                CharacterUnitRoleType.None,
                CharacterColor.None,
                KillerAttackFlag.False,
                AdvantageUnitColorFlag.False);
        }

        public static AppliedAttackResultModel TranslateForNoneDamageType(
            HitAttackResultModel attackResult,
            HP hp,
            BattleSide targetBattleSide)
        {
            var hitAttackResult = attackResult;
            if (hitAttackResult == null || hitAttackResult.IsEmpty())
            {
                return AppliedAttackResultModel.Empty;
            }
            
            var hitData = hitAttackResult.AttackHitData;
            if (hitData.HitType == AttackHitType.Drain)
            {
                hitData = AttackHitData.Normal;
            }

            return new AppliedAttackResultModel(
                hitAttackResult.TargetId,
                targetBattleSide,
                attackResult.AttackerId,
                hitAttackResult.AttackDamageType,
                hitData,
                hitAttackResult.IsHitStop,
                Damage.Zero,
                Heal.Zero,
                Damage.Zero,
                Heal.Zero,
                hp,
                hp,
                hitAttackResult.AttackerRoleType,
                hitAttackResult.AttackerColor,
                KillerAttackFlag.False,
                AdvantageUnitColorFlag.False);
        }
        
        public static AppliedAttackResultModel TranslateForNoneDamageType(
            AttackFeedbackModel feedback, 
            HP hp,
            BattleSide targetBattleSide)
        {
            return new AppliedAttackResultModel(
                feedback.AttackerId,
                targetBattleSide,
                FieldObjectId.Empty,
                feedback.AttackDamageType,
                feedback.AttackHitData,
                AttackHitStopFlag.False,
                Damage.Zero,
                Heal.Zero,
                Damage.Zero,
                Heal.Zero,
                hp,
                hp,
                CharacterUnitRoleType.None,
                CharacterColor.None,
                KillerAttackFlag.False,
                AdvantageUnitColorFlag.False);
        }
    }
}
