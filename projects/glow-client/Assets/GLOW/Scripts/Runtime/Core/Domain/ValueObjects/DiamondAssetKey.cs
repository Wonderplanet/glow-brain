namespace GLOW.Core.Domain.ValueObjects
{
    public record DiamondAssetKey
    {
        public string Value { get; } = "diamond";

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
