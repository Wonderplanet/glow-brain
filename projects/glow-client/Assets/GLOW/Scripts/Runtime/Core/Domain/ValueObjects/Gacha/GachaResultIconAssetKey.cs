namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaResultIconAssetKey(string Value)
    {
        public static UnitAssetKey Empty { get; } = new UnitAssetKey(string.Empty);

        public UnitAssetKey ToUnitAssetKey()
        {
            return new UnitAssetKey(Value);
        }

        public ItemAssetKey ToItemAssetKey()
        {
            return new ItemAssetKey(Value);
        }
    }
}
