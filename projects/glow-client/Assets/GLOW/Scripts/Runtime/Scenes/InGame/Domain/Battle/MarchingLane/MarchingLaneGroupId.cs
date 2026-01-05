namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public record MarchingLaneGroupId(string Value)
    {
        public static MarchingLaneGroupId Empty { get; } = new MarchingLaneGroupId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
