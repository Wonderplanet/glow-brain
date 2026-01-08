using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AttackFeedbackModel(
        FieldObjectId AttackerId,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        AttackPower BasePower,
        AttackPowerParameter PowerParameter)
    {
        public static AttackFeedbackModel Empty { get; } = new(
            FieldObjectId.Empty,
            AttackDamageType.None,
            AttackHitData.Empty,
            AttackPower.Empty,
            AttackPowerParameter.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
