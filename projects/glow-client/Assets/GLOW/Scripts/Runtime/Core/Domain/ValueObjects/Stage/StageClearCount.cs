using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageClearCount(ObscuredInt Value)
    {
        public static StageClearCount Empty { get; } = new (0);
        public static StageClearCount Zero { get; } = new (0);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
        public bool IsCleared => Value > 0;


        public static bool operator <=(StageClearCount a, int b)
        {
            return a.Value <= b;
        }
        public static bool operator <=(int a, StageClearCount b)
        {
            return a <= b.Value;
        }

        public static bool operator >=(StageClearCount a, int b)
        {
            return a.Value >= b;
        }
        public static bool operator >=(int a, StageClearCount b)
        {
            return a >= b.Value;
        }

        public static bool operator >(StageClearCount left, StageClearCount right) => left.Value > right.Value;
        public static bool operator <(StageClearCount left, StageClearCount right) => left.Value < right.Value;
        public static bool operator >=(StageClearCount left, StageClearCount right) => left.Value >= right.Value;
        public static bool operator <=(StageClearCount left, StageClearCount right) => left.Value <= right.Value;
    };
}
