namespace GLOW.Core.Domain.ValueObjects
{
    public record CoinAssetKey
    {
        public string Value { get; } = "coin";

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
