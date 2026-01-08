namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record NewQuestFlag(bool Value)
    {
        public static NewQuestFlag True { get; } = new NewQuestFlag(true);
        public static NewQuestFlag False { get; } = new NewQuestFlag(false);

        public static implicit operator bool(NewQuestFlag flag) => flag.Value;
    }
}
