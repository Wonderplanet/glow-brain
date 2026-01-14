using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects
{
    public record HomeHelpTitle(ObscuredString Value)
    {
        public static HomeHelpTitle Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static implicit operator string(HomeHelpTitle title) => title.Value;
    }
}
