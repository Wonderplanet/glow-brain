using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record Damage(ObscuredInt Value) : IComparable
    {
        public static Damage Empty { get; } = new(0);
        public static Damage Zero { get; } = new(0);

        public static Damage operator +(Damage a, Damage b)
        {
            return new Damage(a.Value + b.Value);
        }

        public static Damage operator *(Damage a, Percentage b)
        {
            return new Damage(Mathf.CeilToInt(a.Value * b.Value / 100f));
        }

        public static Damage operator *(Damage a, float b)
        {
            return new Damage(Mathf.CeilToInt(a.Value * b));
        }

        public static bool operator <(Damage a, Damage b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(Damage a, Damage b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(Damage a, Damage b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(Damage a, Damage b)
        {
            return a.Value >= b.Value;
        }

        public static Damage Min(Damage a, Damage b)
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

        public Heal ToHeal()
        {
            return new Heal(Value);
        }

        public InGameScore ToInGameScore(DamageScoreAdditionalCoef coef)
        {
            if (Value <= 0)
            {
                return InGameScore.Zero;
            }

            // 係数をかけた結果1未満になってもスコア1は獲得できるようにした上で整数に変換
            var score = Math.Max(1, (long)(Value * coef.Value));
            return new InGameScore(score);
        }

        public int CompareTo(object obj)
        {
            if (obj is Damage other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
