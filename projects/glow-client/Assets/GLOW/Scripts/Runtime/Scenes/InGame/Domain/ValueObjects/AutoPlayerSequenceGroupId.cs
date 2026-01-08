using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceGroupId(ObscuredString Value)
    {
        public static AutoPlayerSequenceGroupId Empty { get; } = new(string.Empty);
    }
}
