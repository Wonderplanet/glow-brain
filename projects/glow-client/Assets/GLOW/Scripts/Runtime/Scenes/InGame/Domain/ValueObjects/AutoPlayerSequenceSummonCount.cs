using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceSummonCount(ObscuredInt Value)
    {
        public static AutoPlayerSequenceSummonCount Empty { get; } = new(0);
        public static AutoPlayerSequenceSummonCount Infinity { get; } = new AutoPlayerSequenceSummonCount(int.MaxValue);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static AutoPlayerSequenceSummonCount operator -(AutoPlayerSequenceSummonCount a, int b)
        {
            return new AutoPlayerSequenceSummonCount(a.Value - b);
        }
        
        public static AutoPlayerSequenceSummonCount operator +(AutoPlayerSequenceSummonCount a, AutoPlayerSequenceSummonCount b)
        {
            if (a.IsInfinity() || b.IsInfinity()) return Infinity;
            
            return new AutoPlayerSequenceSummonCount(a.Value + b.Value);
        }
        
        public static bool operator <(AutoPlayerSequenceSummonCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(AutoPlayerSequenceSummonCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(AutoPlayerSequenceSummonCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(AutoPlayerSequenceSummonCount a, int b)
        {
            return a.Value >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
        
        public BossCount ToBossCount()
        {
            return new BossCount(Value);
        }
    }
}
