using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record Stamina(ObscuredInt Value)
    {
        public static Stamina Empty { get; } = new(0);

        public static Stamina operator +(Stamina a, Stamina b)
        {
            return new(a.Value + b.Value);
        }

        public static Stamina operator +(Stamina a, int b)
        {
            return new(a.Value + b);
        }

        public static Stamina operator -(Stamina a, Stamina b)
        {
            return new(a.Value - b.Value);
        }

        public static Stamina operator -(Stamina a, int b)
        {
            return new(a.Value - b);
        }

        public static bool operator >(Stamina a, Stamina b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(Stamina a, Stamina b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(Stamina a, Stamina b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(Stamina a, Stamina b)
        {
            return a.Value <= b.Value;
        }

        public static Stamina Min(Stamina a, Stamina b)
        {
            return new Stamina(Mathf.Min(a.Value, b.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    };
}
