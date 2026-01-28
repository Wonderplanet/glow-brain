namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DarknessClearedFlag(bool Value)
    {
        public static DarknessClearedFlag True { get; } = new(true);
        public static DarknessClearedFlag False { get; } = new(false);

        public static implicit operator bool(DarknessClearedFlag flag) => flag.Value;

        public static bool operator true(DarknessClearedFlag flag) => flag.Value;
        public static bool operator false(DarknessClearedFlag flag) => !flag.Value;
        public static DarknessClearedFlag operator |(DarknessClearedFlag left, DarknessClearedFlag right) => new (left.Value | right.Value);
    }
}
