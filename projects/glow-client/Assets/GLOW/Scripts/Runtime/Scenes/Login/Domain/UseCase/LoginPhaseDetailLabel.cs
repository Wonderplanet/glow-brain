namespace GLOW.Scenes.Login.Domain.UseCase
{
    public record LoginPhaseDetailLabel(long Downloaded, long Total, float BytePerSec)
    {
        // intで渡してpresentation側でフォーマットするようにする
        public long Downloaded { get; } = Downloaded;
        public long Total { get; } = Total;
        public float BytePerSec { get; } = BytePerSec;
    }
}
