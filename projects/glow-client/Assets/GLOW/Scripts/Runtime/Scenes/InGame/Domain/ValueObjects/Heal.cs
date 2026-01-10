using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record Heal(ObscuredInt Value)
    {
        public static Heal Empty { get; } = new(0);
        public static Heal Zero { get; } = new(0);

        public static Heal operator +(Heal a, Heal b)
        {
            return new Heal(a.Value + b.Value);
        }

        public static Heal operator *(Heal a, Percentage b)
        {
            return new Heal(Mathf.CeilToInt(a.Value * b.Value / 100f));
        }

        public static Heal Min(Heal a, Heal b)
        {
            return a.Value < b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
        
        public float ToFloat()
        {
            return Value;
        }
    }
}
