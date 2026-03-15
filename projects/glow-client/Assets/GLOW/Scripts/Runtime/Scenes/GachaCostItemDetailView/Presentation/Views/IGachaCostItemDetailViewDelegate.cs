using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.Views
{
    public interface IGachaCostItemDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseButtonTapped();
        void OnTransitionButtonTapped();
    }
}