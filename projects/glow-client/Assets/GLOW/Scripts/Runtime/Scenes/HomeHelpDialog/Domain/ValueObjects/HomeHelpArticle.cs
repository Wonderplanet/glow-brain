using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects
{
    public record HomeHelpArticle(ObscuredString Value)
    {
        public static HomeHelpArticle Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static implicit operator string(HomeHelpArticle article) => article.Value;
    }
}
