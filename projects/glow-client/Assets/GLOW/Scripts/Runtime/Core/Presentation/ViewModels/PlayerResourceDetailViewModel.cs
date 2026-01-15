using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;

namespace GLOW.Core.Presentation.ViewModels
{
    public record PlayerResourceDetailViewModel(
        PlayerResourceIconViewModel iconViewModel,
        PlayerResourceName Name,
        PlayerResourceDescription Description,
        ResourceType Type,
        PlayerResourceAmount Amount,
        bool IsHideCurrentAmount);
}
