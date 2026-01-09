using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record HPCalculatorResultDetailModel(
        HitAttackResultModel AttackResult,
        Damage Damage,
        Heal Heal,
        Damage AppliedDamage,
        Heal AppliedHeal,
        HP BeforeHp,
        HP AfterHp,
        KillerAttackFlag IsKillerAttack,
        AdvantageUnitColorFlag IsAdvantageUnitColor)
    {
        public static HPCalculatorResultDetailModel Empty { get; } = new(
            HitAttackResultModel.Empty,
            Damage.Empty, 
            Heal.Empty,
            Damage.Empty,
            Heal.Empty,
            HP.Empty,
            HP.Empty,
            KillerAttackFlag.False,
            AdvantageUnitColorFlag.False);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
