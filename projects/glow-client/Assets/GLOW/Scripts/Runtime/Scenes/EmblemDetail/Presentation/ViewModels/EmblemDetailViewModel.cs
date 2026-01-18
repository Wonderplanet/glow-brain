using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EmblemDetail.Presentation.ViewModels
{
    public record EmblemDetailViewModel(
        EmblemIconAssetPath IconAssetPath,
        EmblemName Name,
        EmblemDescription Description
    );
}
