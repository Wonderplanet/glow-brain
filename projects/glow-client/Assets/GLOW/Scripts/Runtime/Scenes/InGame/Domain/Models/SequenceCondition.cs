using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SequenceCondition(
        AutoPlayerSequenceConditionType Type,
        AutoPlayerSequenceConditionValue Value)
    {
        public static SequenceCondition Empty { get; } = new(
            AutoPlayerSequenceConditionType.None,
            AutoPlayerSequenceConditionValue.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
