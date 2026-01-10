using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EmblemDetail.Domain.Models
{
    public record EmblemDetailModel(
        EmblemIconAssetPath IconAssetPath,
        EmblemName Name,
        EmblemDescription Description
    );
}
