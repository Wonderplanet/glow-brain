using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.GachaDetailDialog.Domain.Models
{
    public record GachaCautionContentsUrl(ObscuredString Value)
    {
        public static GachaCautionContentsUrl Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}