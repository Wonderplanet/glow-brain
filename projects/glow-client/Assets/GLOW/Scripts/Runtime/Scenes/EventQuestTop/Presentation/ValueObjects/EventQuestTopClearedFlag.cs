namespace GLOW.Scenes.EventQuestTop.Presentation.ValueObjects
{
    public record EventQuestTopClearedFlag(bool Value)
    {
        public static EventQuestTopClearedFlag True { get; } = new EventQuestTopClearedFlag(true);
        public static EventQuestTopClearedFlag False { get; } = new EventQuestTopClearedFlag(false);

        public static implicit operator bool(EventQuestTopClearedFlag flag) => flag.Value;
    }
}
