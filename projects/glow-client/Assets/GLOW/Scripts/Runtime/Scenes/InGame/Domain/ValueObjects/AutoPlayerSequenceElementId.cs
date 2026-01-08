using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceElementId(ObscuredString Value)
    {
        public static AutoPlayerSequenceElementId Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
