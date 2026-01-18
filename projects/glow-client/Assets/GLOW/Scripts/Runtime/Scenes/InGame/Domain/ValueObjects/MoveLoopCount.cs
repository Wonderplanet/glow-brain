using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// AutoPlayerSequenceにて、移動->移動停止->再移動するときに何回「停止->再移動」を行うかのループ値
    /// </summary>
    public record MoveLoopCount(ObscuredInt Value)
    {
        public static MoveLoopCount Empty { get; } = new(-1);
        public static MoveLoopCount One { get; } = new(1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsLoopValid()
        {
            return this > 0;
        }

        public static bool operator <(MoveLoopCount a, int b) => a.Value < b;
        public static bool operator >(MoveLoopCount a, int b) => a.Value > b;
        public static bool operator <=(MoveLoopCount a, int b) => a.Value <= b;
        public static bool operator >=(MoveLoopCount a, int b) => a.Value >= b;

        public static MoveLoopCount operator +(MoveLoopCount a, int b) => new MoveLoopCount(a.Value + b);
        public static MoveLoopCount operator -(MoveLoopCount a, int b) => new MoveLoopCount(a.Value - b);

    }
}
