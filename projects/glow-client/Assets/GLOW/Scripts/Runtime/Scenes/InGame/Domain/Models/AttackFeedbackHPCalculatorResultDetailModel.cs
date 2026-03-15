using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AttackFeedbackHPCalculatorResultDetailModel(
        AttackFeedbackModel AttackFeedback,
        Heal Heal,
        Heal AppliedHeal,
        HP BeforeHp,
        HP AfterHp)
    {
        public static AttackFeedbackHPCalculatorResultDetailModel Empty { get; } = new(
            AttackFeedbackModel.Empty,
            Heal.Empty,
            Heal.Empty,
            HP.Empty,
            HP.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
