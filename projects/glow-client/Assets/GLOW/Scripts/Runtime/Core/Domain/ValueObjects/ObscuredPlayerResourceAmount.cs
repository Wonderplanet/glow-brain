using System.Globalization;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Pass;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ObscuredPlayerResourceAmount(ObscuredInt Value)
    {
        public static ObscuredPlayerResourceAmount Empty { get; } = new(0);

        public static ObscuredPlayerResourceAmount operator *(ObscuredPlayerResourceAmount a, PassDurationDay b)
        {
            return new ObscuredPlayerResourceAmount(a.Value * b.Value);
        }
        
        public static bool operator ==(ObscuredPlayerResourceAmount left, PlayerResourceAmount right)
        {
            if ((object)left == null && (object)right == null) return true;
            if ((object)left == null || (object)right == null) return false;
            
            return left.Value == right.Value;
        }
        
        public static bool operator !=(ObscuredPlayerResourceAmount left, PlayerResourceAmount right)
        {
            if ((object)left == null && (object)right == null) return false;
            if ((object)left == null || (object)right == null) return true;
            
            return left.Value != right.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return IsEmpty() ? "" : Value.ToString();
        }
        
        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Value);
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
            return IsEmpty() ? "" : Value.ToString("N0", CultureInfo.InvariantCulture);
        }
    }
}