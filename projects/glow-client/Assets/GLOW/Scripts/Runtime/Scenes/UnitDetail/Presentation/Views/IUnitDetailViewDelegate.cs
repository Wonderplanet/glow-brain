namespace GLOW.Scenes.UnitDetail.Presentation.Views
{
    public interface IUnitDetailViewDelegate
    {
        void ViewDidLoad();
        void OnBackButtonTapped();
        void OnSpecialAttackDetailButtonTapped();
        void OnDetailTabButtonTapped();
        void OnSpecialAttackTabButtonTapped();
        void OnAbilityTabButtonTapped();
        void OnStatusTabButtonTapped();
    }
}
