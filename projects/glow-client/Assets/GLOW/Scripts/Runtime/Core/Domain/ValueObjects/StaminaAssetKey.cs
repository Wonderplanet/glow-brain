namespace GLOW.Core.Domain.ValueObjects
{
    public record StaminaAssetKey
    {
        public string Value { get; } = "stamina";

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}