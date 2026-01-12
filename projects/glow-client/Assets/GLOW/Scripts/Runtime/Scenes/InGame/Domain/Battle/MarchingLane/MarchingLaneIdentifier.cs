namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public record MarchingLaneIdentifier(
        MarchingLaneGroupId LaneGroupId,
        MarchingLaneIndex LaneIndex,
        MarchingLaneIndex LaneIndexOffset)
    {
        public static MarchingLaneIdentifier Empty { get; } = new MarchingLaneIdentifier(
            MarchingLaneGroupId.Empty,
            MarchingLaneIndex.Empty,
            MarchingLaneIndex.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
