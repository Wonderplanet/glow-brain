namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record NewStageExistenceFlag(bool Value)
    {
        public static NewStageExistenceFlag True { get; } = new NewStageExistenceFlag(true);
        public static NewStageExistenceFlag False { get; } = new NewStageExistenceFlag(false);

        public static implicit operator bool(NewStageExistenceFlag flag) => flag.Value;
    }
}
