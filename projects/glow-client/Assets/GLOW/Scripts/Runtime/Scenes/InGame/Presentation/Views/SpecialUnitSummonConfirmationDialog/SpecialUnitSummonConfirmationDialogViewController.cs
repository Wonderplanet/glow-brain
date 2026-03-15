using System;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog
{
    public class SpecialUnitSummonConfirmationDialogViewController :
        UIViewController<SpecialUnitSummonConfirmationDialogView>,
        IEscapeResponder
    {
        public record Argument(SpecialUnitSummonConfirmationDialogViewModel ViewModel);

        public Action OnUseSkill { get; set; }
        public Action OnCancel { get; set; }

        [Inject] ISpecialUnitSummonConfirmationDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(SpecialUnitSummonConfirmationDialogViewModel viewModel)
        {
            ActualView.SetSpecialAttackName(viewModel.SpecialAttackName);
            ActualView.SetSpecialAttackDescription(viewModel.SpecialAttackInfoDescription);
            ActualView.SetSpecialAttackCost(viewModel.SummonCost);
            ActualView.SetUseSkillButtonVisible(viewModel.NeedTargetSelectTypeFlag);
        }

        public void Close()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            UISoundEffector.Main.PlaySeEscape();
            OnCancelButton();

            return true;
        }

        [UIAction]
        void OnUseSkillButton()
        {
            ViewDelegate.OnUseSkillButton();
        }

        [UIAction]
        void OnCancelButton()
        {
            ViewDelegate.OnCancelButton();
        }
    }
}
