namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public enum StageStatus
    {
        UnRelease,//開放条件ステージクリアしてない
        UnReleaseAtOutOfTime,
        Released,

    }

    public record StageReleaseStatus(StageStatus Value)
    {
        public static StageReleaseStatus Empty { get; } = new(StageStatus.UnRelease);
        public bool IsReleased => Value == StageStatus.Released;
        public StagePlayableFlag ToStagePlayableFlag()
        {
            return new StagePlayableFlag(Value == StageStatus.Released);
        }
    };

}
