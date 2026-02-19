using GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    /// <summary> スペシャルユニット必殺技発動前の確認ダイアログ表示 </summary>
    public class SpecialUnitSummonConfirmationDialogPresenter : ISpecialUnitSummonConfirmationDialogViewDelegate
    {
        [Inject] SpecialUnitSummonConfirmationDialogViewController ViewController { get; }
        [Inject] SpecialUnitSummonConfirmationDialogViewController.Argument Argument { get; }

        void ISpecialUnitSummonConfirmationDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Setup(Argument.ViewModel);
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }

        public void OnUseSkillButton()
        {
            ViewController.OnUseSkill();
        }

        public void OnCancelButton()
        {
            ViewController.OnCancel();
        }
    }
}
