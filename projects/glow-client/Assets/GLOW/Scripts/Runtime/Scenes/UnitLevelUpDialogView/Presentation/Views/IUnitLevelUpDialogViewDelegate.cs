using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public interface IUnitLevelUpDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnCloseButtonTapped();
        void OnEnhanceButtonTapped(UnitLevel selectLevel);
        void OnRankUpDetailButtonTapped();
    }
}
