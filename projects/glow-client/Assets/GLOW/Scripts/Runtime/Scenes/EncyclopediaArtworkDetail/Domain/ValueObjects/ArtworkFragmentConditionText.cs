using Cysharp.Text;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects
{
    public record ArtworkFragmentConditionText(string Value)
    {
        public static ArtworkFragmentConditionText Empty = new(string.Empty);
    }
}
