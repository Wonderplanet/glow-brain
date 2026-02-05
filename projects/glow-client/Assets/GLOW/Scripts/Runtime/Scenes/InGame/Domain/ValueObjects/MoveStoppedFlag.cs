namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> AutoPlayerSequenceにて、移動停止中かの判別となるフラグ </summary>
    public record MoveStoppedFlag(bool Value)
    {
        public static MoveStoppedFlag True { get; } = new(true);
        public static MoveStoppedFlag False { get; } = new(false);

        public static implicit operator bool(MoveStoppedFlag flag) => flag.Value;

        public static bool operator true(MoveStoppedFlag flag) => flag.Value;
        public static bool operator false(MoveStoppedFlag flag) => !flag.Value;
    }
}
