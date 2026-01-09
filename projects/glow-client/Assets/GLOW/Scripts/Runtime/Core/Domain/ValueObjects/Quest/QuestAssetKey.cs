using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record QuestAssetKey(ObscuredString Value)
    {
        public static QuestAssetKey Empty { get; } = new QuestAssetKey("");
        public string ToEventAddressablePath() => $"event_{Value}";
    };
}