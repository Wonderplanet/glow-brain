namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record StateEffectInvalidationFlag(bool Value)
    {
        public static StateEffectInvalidationFlag True { get; } = new(true);
        public static StateEffectInvalidationFlag False { get; } = new(false);

        public static implicit operator bool(StateEffectInvalidationFlag flag) => flag.Value;

        public static bool operator true(StateEffectInvalidationFlag flag) => flag.Value;
        public static bool operator false(StateEffectInvalidationFlag flag) => !flag.Value;
        public static StateEffectInvalidationFlag operator |(StateEffectInvalidationFlag left, StateEffectInvalidationFlag right) => new (left.Value | right.Value);
    }
}
