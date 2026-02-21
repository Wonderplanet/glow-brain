using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkEffectDescription(string Value)
    {
        public static ArtworkEffectDescription FromHp(HP value)
        {
            return new ArtworkEffectDescription(ZString.Format("ゲートHP+{0}", value.Value));
        }
    }
}
