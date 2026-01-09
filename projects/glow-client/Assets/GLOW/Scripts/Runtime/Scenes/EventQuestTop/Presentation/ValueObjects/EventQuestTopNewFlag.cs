namespace GLOW.Scenes.EventQuestTop.Presentation.ValueObjects
{
    public record EventQuestTopNewFlag(bool Value)
    {
        public static EventQuestTopNewFlag True { get; } = new EventQuestTopNewFlag(true);
        public static EventQuestTopNewFlag False { get; } = new EventQuestTopNewFlag(false);

        public static implicit operator bool(EventQuestTopNewFlag flag) => flag.Value;
    }
}
