using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticeDestinationPath(ObscuredString Value)
    {
        public static NoticeDestinationPath Empty { get; } = new NoticeDestinationPath("");
        public static NoticeDestinationPath Pass { get; } = new NoticeDestinationPath("Pass");
        public static NoticeDestinationPath ShopPaid { get; } = new NoticeDestinationPath("ShopPaid");
        public static NoticeDestinationPath ShopFree { get; } = new NoticeDestinationPath("ShopFree");
        public static NoticeDestinationPath Pvp { get; } = new NoticeDestinationPath("Pvp");
        public static NoticeDestinationPath Exchange { get; } = new NoticeDestinationPath("Exchange");

        public DestinationScene ToDestinationScene()
        {
            return new DestinationScene(Value);
        }

        public bool IsShopPath()
        {
            return Value == ShopPaid.Value || Value == ShopFree.Value || Value == Pass.Value;
        }

        public bool IsPvpPath()
        {
            return Value == Pvp.Value;
        }
        public bool IsExchangePath()
        {
            return Value == Exchange.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
