using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AutoPlayerSequenceAction(
        AutoPlayerSequenceActionType Type,
        AutoPlayerSequenceActionValue Value,
        AutoPlayerSequenceActionValue Value2)
    {
        public static AutoPlayerSequenceAction Empty { get; } = new(
            AutoPlayerSequenceActionType.None,
            AutoPlayerSequenceActionValue.Empty,
            AutoPlayerSequenceActionValue.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
