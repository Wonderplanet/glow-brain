using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Presenters
{
    public class UnitEnhanceGradeUpConfirmDialogPresenter : IUnitEnhanceGradeUpConfirmDialogViewDelegate
    {
        [Inject] UnitEnhanceGradeUpConfirmDialogViewController ViewController { get; }
        [Inject] UnitEnhanceGradeUpConfirmDialogViewController.Argument Argument { get; }
        [Inject] GetUnitEnhanceGradeUpConfirmModelUseCase GetUnitEnhanceGradeUpConfirmModelUseCase { get; }
        [Inject] GetUnitEnhanceSpecialAttackInfoUseCase GetUnitEnhanceSpecialAttackInfoUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }

        void IUnitEnhanceGradeUpConfirmDialogViewDelegate.OnViewDidLoad()
        {
            var model = GetUnitEnhanceGradeUpConfirmModelUseCase.GetModel(Argument.UserUnitId);
            var viewModel =TranslateViewModel(model);
            ViewController.Setup(viewModel);
        }

        void IUnitEnhanceGradeUpConfirmDialogViewDelegate.OnViewDidAppear()
        {
            ViewController.PlayCostItemAppearanceAnimation();
        }

        void IUnitEnhanceGradeUpConfirmDialogViewDelegate.OnConfirmButtonTapped()
        {
            ViewController.Dismiss();
            Argument.OnConfirm?.Invoke();
        }

        void IUnitEnhanceGradeUpConfirmDialogViewDelegate.OnCancelButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IUnitEnhanceGradeUpConfirmDialogViewDelegate.OnGradeUpDetailButtonTapped()
        {
            var model = GetUnitEnhanceSpecialAttackInfoUseCase.GetUnitEnhanceSpecialAttackInfoModel(Argument.UserUnitId);
            var argument = new SpecialAttackInfoViewController.Argument(model.MstUnitId, model.UnitGrade, model.UnitLevel);
            var controller = ViewFactory.Create<SpecialAttackInfoViewController, SpecialAttackInfoViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        UnitEnhanceGradeUpConfirmViewModel TranslateViewModel(UnitEnhanceGradeUpConfirmModel model)
        {
            return new UnitEnhanceGradeUpConfirmViewModel(
                model.RoleType,
                ItemViewModelTranslator.ToItemIconViewModel(model.Item),
                model.BeforeGrade,
                model.AfterGrade,
                model.PossessionAmount,
                model.BeforeHp,
                model.AfterHp,
                model.BeforeAttackPower,
                model.AfterAttackPower,
                model.SpecialAttackName,
                model.Description,
                model.EnableConfirm
            );
        }
    }
}
