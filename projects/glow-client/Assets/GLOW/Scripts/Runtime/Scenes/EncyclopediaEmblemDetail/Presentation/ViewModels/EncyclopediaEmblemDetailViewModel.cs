using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.ViewModels
{
    public record EncyclopediaEmblemDetailViewModel(
        EmblemIconAssetPath IconAssetPath,
        EmblemName Name,
        EmblemDescription Description
    );
}
