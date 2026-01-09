namespace GLOW.Scenes.GameModeSelect.Domain
{
    public record GameModeSelectAssetKey(string Value)
    {
        public static GameModeSelectAssetKey Empty { get; } = new GameModeSelectAssetKey(string.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
