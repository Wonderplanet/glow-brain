using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeFooterViewModel(
        NotificationBadge Gacha,
        NotificationBadge Character,
        NotificationBadge Home,
        NotificationBadge Content,
        NotificationBadge Shop);

}
