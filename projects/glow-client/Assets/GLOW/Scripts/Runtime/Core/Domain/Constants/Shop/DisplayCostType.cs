namespace GLOW.Core.Domain.Constants.Shop
{
    public enum DisplayCostType
    {
        Coin = 0,
        Diamond = 1,
        PaidDiamond = 2,
        Ad = 3,
        Cash = 4,
        Free = 5,
    }

    public static class DisplayCostTypeExtensions
    {
        public static DisplayCostType ToDisplayShopProductType(this CostType type)
        {
            return type switch
            {
                CostType.Coin => DisplayCostType.Coin,
                CostType.Diamond => DisplayCostType.Diamond,
                CostType.Ad => DisplayCostType.Ad,
                CostType.Free => DisplayCostType.Free,
                _ => DisplayCostType.Cash
            };
        }
    }
}