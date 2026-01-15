using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.Models;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.UseCases;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Presenters
{
    public class UnitLevelUpDialogPresenter : IUnitLevelUpDialogViewDelegate
    {
        [Inject] UnitLevelUpDialogViewController ViewController { get; }
        [Inject] UnitLevelUpDialogViewController.Argument Argument { get; }
        [Inject] GetUnitLevelUpDialogModelUseCase GetUnitLevelUpDialogModelUseCase { get; }
        [Inject] ExecuteUnitLevelUpUseCase ExecuteUnitLevelUpUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public void OnViewDidLoad()
        {
            var model = GetUnitLevelUpDialogModelUseCase.GetLevelUpModel(Argument.UserUnitId);
            var viewModel = TranslateLevelUpDialogViewModel(model);
            ViewController.Setup(viewModel);
        }
        
        public void OnViewDidAppear()
        {
            ViewController.PlayResourceAppearanceAnimation();
        }

        public void OnCloseButtonTapped()
        {
            CloseDialog(false);
        }

        public void OnEnhanceButtonTapped(UnitLevel selectLevel)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ExecuteUnitLevelUpUseCase.ExecuteUnitLevelUp(cancellationToken, Argument.UserUnitId, selectLevel);
                HomeHeaderDelegate.UpdateStatus();
                CloseDialog(true);
            });
        }

        public void OnRankUpDetailButtonTapped()
        {
            var args = new UnitEnhanceRankUpDetailDialogViewController.Argument(Argument.UserUnitId);
            var viewController = ViewFactory
                .Create<UnitEnhanceRankUpDetailDialogViewController, UnitEnhanceRankUpDetailDialogViewController.Argument>(args);
            ViewController.PresentModally(viewController);
        }

        UnitLevelUpDialogViewModel TranslateLevelUpDialogViewModel(UnitLevelUpDialogModel model)
        {
            var values = model.LevelValues
                .Select(TranslateLevelUpValueViewModel)
                .ToList();
            return new UnitLevelUpDialogViewModel(
                model.RoleType,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.IconModel),
                model.CurrentLevel,
                model.PossessionCoin,
                model.CurrentHp,
                model.CurrentAttackPower,
                values);
        }

        UnitLevelUpValueViewModel TranslateLevelUpValueViewModel(UnitLevelUpValueModel model)
        {
            return new UnitLevelUpValueViewModel(
                model.Level,
                model.ConsumeCoinValue,
                model.ConsumedCoin,
                model.AfterHP,
                model.AfterAttackPower,
                model.SpecialAttackName,
                model.SpecialAttackDescription,
                model.ButtonState);
        }

        void CloseDialog(bool isLevelUp)
        {
            ViewController.Dismiss();
            Argument.OnClose?.Invoke(isLevelUp);
        }
    }
}
