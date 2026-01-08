namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayingTutorialSequenceFlag(bool Value)
    {
        public static PlayingTutorialSequenceFlag True { get; } = new PlayingTutorialSequenceFlag(true);
        public static PlayingTutorialSequenceFlag False { get; } = new PlayingTutorialSequenceFlag(false);

        public static implicit operator bool(PlayingTutorialSequenceFlag playingTutorialSequenceFlag) => playingTutorialSequenceFlag.Value;
    }
}