namespace GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog
{
    public interface ISpecialUnitSummonConfirmationDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnClose();
        void OnUseSkillButton();
        void OnCancelButton();
    }
}
