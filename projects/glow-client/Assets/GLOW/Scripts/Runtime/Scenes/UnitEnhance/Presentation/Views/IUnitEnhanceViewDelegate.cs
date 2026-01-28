using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views
{
    public interface IUnitEnhanceViewDelegate
    {
        void ViewDidLoad();
        void OnBackButtonTapped();
        void OnEnhanceTabButtonTapped();
        void OnGradeUpTabButtonTapped();
        void OnDetailTabButtonTapped();
        void OnSpecialAttackTabButtonTapped();
        void OnAbilityTabButtonTapped();
        void OnStatusTabButtonTapped();
        void OnLevelUpButtonTapped();
        void OnRankUpButtonTapped();
        void OnGradeUpButtonTapped();
        void OnSpecialAttackDetailButtonTapped();
        void SwitchUnit(MasterDataId mstUnitId);
    }
}
