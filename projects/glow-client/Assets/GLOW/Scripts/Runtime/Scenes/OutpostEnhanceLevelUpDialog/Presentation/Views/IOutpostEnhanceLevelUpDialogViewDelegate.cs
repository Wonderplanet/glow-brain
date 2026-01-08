using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views
{
    public interface IOutpostEnhanceLevelUpDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseButtonTapped();
        void OnEnhanceButtonTapped(OutpostEnhanceLevel selectLevel);
    }
}
