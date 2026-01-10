using Cysharp.Text;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public record GameModeSelectAssetPath(string Value)
    {
        const string AssetPath = "gamemode_{0}";
        public static GameModeSelectAssetPath ToButtonAssetPath(GameModeSelectAssetKey assetKey) => new GameModeSelectAssetPath(ZString.Format(AssetPath, assetKey.Value));

    }
}
