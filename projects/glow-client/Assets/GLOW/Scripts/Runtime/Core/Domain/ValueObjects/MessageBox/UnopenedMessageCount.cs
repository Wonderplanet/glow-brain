using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record UnopenedMessageCount(ObscuredInt Value)
    {
        public static UnopenedMessageCount Empty { get; } = new(0);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsZero()
        {
            return Value == 0;
        }
    }
}