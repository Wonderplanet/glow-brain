namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public record MarchingLaneIndex(int Value)
    {
        public static MarchingLaneIndex Empty { get; } = new MarchingLaneIndex(-1);
        public static MarchingLaneIndex Zero { get; } = new MarchingLaneIndex(0);

        public static MarchingLaneIndex operator +(MarchingLaneIndex a, int b) => new (a.Value + b);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
