using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Shop.Domain.Constants
{
    public static class ShopConst
    {
        public static MasterDataId DiamondId = new MasterDataId("2");
        public static UserAge YoungAge = new UserAge(16);
        public static UserAge AdultAge = new UserAge(18);
        public static int YoungPurchaseLimit = 5000;
        public static int AdultPurchaseLimit = 20000;
    }
}
