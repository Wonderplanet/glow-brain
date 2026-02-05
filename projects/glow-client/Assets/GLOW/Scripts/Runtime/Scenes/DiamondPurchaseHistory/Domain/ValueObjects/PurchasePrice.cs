namespace GLOW.Scenes.DiamondPurchaseHistory.Domain
{
    public record PurchasePrice(string Value)
    {
        public string ToMoneyString()
        {
            return Value;
        }
    };
}
