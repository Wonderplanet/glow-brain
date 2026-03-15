namespace GLOW.Core.Domain.ValueObjects
{
    public record UserExpAssetKey
    {
        public string Value { get; } = "user_exp";

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
