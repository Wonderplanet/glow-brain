namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record GimmickObjectTransformationStartedFlag(bool Value)
    {
        public static GimmickObjectTransformationStartedFlag True { get; } = new(true);
        public static GimmickObjectTransformationStartedFlag False { get; } = new(false);

        public static implicit operator bool(GimmickObjectTransformationStartedFlag flag) => flag.Value;
    }
}
