namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> InGameTypeがPvpかどうかのフラグ </summary>
    public record InGameTypePvpFlag(bool Value)
    {
        public static InGameTypePvpFlag True { get; } = new(true);
        public static InGameTypePvpFlag False { get; } = new(false);

        public static implicit operator bool(InGameTypePvpFlag flag) => flag.Value;
    }
}
