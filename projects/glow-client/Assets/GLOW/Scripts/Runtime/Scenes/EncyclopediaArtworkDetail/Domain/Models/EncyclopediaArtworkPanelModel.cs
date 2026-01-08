using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models
{
    public record EncyclopediaArtworkPanelModel(
        ArtworkPanelModel Artwork,
        ArtworkUnlockFlag IsArtworkUnlock
        );
}
