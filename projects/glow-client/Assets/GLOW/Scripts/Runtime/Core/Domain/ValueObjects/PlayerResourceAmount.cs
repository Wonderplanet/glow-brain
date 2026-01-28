using System;
using Cysharp.Text;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceAmount(int Value)
    {
        public static PlayerResourceAmount Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static PlayerResourceAmount operator *(PlayerResourceAmount a, PassDurationDay b)
        {
            return new PlayerResourceAmount(a.Value * b.Value);
        }
        
        public override string ToString()
        {
            return IsEmpty() ? "" : Value.ToString();
        }

        public string ToStringWithMultiplication()
        {
            return IsEmpty() ? "" : ZString.Format("×{0}", Value);
        }

        public string ToStringWithMultiplicationAndSeparate()
        {
            return IsEmpty() ? "" : ZString.Format("×{0:N0}", Value);
        }

        public string ToStringSeparated()
        {
            return IsEmpty() ? "" : Value.ToString("N0");
        }
        
        public ItemAmount ToItemAmount()
        {
            return new ItemAmount(Value);
        }
    }
}
