using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AppliedAttackResultModel(
        FieldObjectId TargetId,
        BattleSide TargetBattleSide,
        FieldObjectId AttackerId,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        AttackHitStopFlag IsHitStop,
        Damage Damage,
        Heal Heal,
        Damage AppliedDamage,
        Heal AppliedHeal,
        HP BeforeHp,
        HP AfterHp,
        CharacterUnitRoleType AttackerRoleType,
        CharacterColor AttackerColor,
        KillerAttackFlag IsKiller,
        AdvantageUnitColorFlag IsAdvantageColor)
    {
        public static AppliedAttackResultModel Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            FieldObjectId.Empty,
            AttackDamageType.None,
            AttackHitData.Empty,
            AttackHitStopFlag.False,
            Damage.Empty,
            Heal.Empty,
            Damage.Empty,
            Heal.Empty,
            HP.Empty,
            HP.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            KillerAttackFlag.False,
            AdvantageUnitColorFlag.False);
    }
}
