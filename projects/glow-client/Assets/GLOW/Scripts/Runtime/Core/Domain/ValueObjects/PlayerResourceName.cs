using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceName(ObscuredString Value)
    {
        public static PlayerResourceName Empty { get; } = new PlayerResourceName("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public ItemName ToItemName()
        {
            return new ItemName(Value);
        }
        
        public static PlayerResourceName TranslateFromItemName(ItemName itemName)
        {
            return new PlayerResourceName(itemName.Value);
        }

        public override string ToString()
        {
            return Value;
        }
    }
}