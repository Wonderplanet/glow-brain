namespace GLOW.Core.Domain.ValueObjects
{
    public record PointAssetKey(string Value)
    {
        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
