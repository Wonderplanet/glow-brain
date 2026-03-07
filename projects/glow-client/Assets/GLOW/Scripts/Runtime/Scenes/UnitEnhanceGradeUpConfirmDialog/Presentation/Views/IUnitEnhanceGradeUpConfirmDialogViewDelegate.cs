namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views
{
    public interface IUnitEnhanceGradeUpConfirmDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnConfirmButtonTapped();
        void OnCancelButtonTapped();
        void OnGradeUpDetailButtonTapped();
    }
}
