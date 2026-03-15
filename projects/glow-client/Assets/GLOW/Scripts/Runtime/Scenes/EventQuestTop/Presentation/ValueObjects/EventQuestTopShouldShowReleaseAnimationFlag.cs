namespace GLOW.Scenes.EventQuestTop.Presentation.ValueObjects
{
    public record EventQuestTopShouldShowReleaseAnimationFlag(bool Value)
    {
        public static EventQuestTopShouldShowReleaseAnimationFlag True { get; } = new EventQuestTopShouldShowReleaseAnimationFlag(true);
        public static EventQuestTopShouldShowReleaseAnimationFlag False { get; } = new EventQuestTopShouldShowReleaseAnimationFlag(false);

        public static implicit operator bool(EventQuestTopShouldShowReleaseAnimationFlag flag) => flag.Value;
    }
}
