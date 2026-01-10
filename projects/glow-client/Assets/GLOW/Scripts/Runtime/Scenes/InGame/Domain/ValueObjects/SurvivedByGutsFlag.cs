namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record SurvivedByGutsFlag(bool Value)
    {
        public static SurvivedByGutsFlag True { get; } = new(true);
        public static SurvivedByGutsFlag False { get; } = new(false);

        public static implicit operator bool(SurvivedByGutsFlag flag) => flag.Value;
    }
}
