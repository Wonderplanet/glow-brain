using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.ViewModels
{
    public record GachaCostItemDetailViewModel(
        PlayerResourceIconViewModel IconViewModel,
        PlayerResourceName Name,
        PlayerResourceDescription Description,
        TransitionButtonGrayOutFlag IsTransitionButtonGrayOut,
        ShowTransitAreaFlag IsTransitAreaVisible);
}