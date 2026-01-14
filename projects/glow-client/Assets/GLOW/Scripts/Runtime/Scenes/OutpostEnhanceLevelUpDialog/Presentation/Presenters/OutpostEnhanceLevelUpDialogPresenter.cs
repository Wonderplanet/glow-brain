using System.Linq;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.Models;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.UseCases;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.ViewModels;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Presenters
{
    public class OutpostEnhanceLevelUpDialogPresenter : IOutpostEnhanceLevelUpDialogViewDelegate
    {
        [Inject] OutpostEnhanceLevelUpDialogViewController ViewController { get; }
        [Inject] OutpostEnhanceLevelUpDialogViewController.Argument Argument { get; }
        [Inject] GetOutpostEnhanceLevelUpDialogModelUseCase GetOutpostEnhanceLevelUpDialogModelUseCase { get; }

        public void OnViewDidLoad()
        {
            var model = GetOutpostEnhanceLevelUpDialogModelUseCase.GetLevelUpModel(Argument.MstEnhanceId);
            var viewModel = TranslateLevelUpDialogViewModel(model);
            ViewController.Setup(viewModel);
        }

        public void OnCloseButtonTapped()
        {
            CloseDialog(false);
        }

        public void OnEnhanceButtonTapped(OutpostEnhanceLevel selectLevel)
        {
            CloseDialog(true, selectLevel);
        }

        OutpostEnhanceLevelUpDialogViewModel TranslateLevelUpDialogViewModel(OutpostEnhanceLevelUpDialogModel model)
        {
            var values = model.LevelValues
                .Select(TranslateLevelUpValueViewModel)
                .ToList();
            return new OutpostEnhanceLevelUpDialogViewModel(model.CurrentLevel, model.PossessionCoin, values);
        }

        OutpostEnhanceLevelUpValueViewModel TranslateLevelUpValueViewModel(OutpostEnhanceLevelUpValueModel model)
        {
            return new OutpostEnhanceLevelUpValueViewModel(model.Level, model.RequiredCoin, model.ConsumedCoin, model.ButtonState);
        }

        void CloseDialog(bool isLevelUp, OutpostEnhanceLevel afterLevel = null)
        {
            ViewController.Dismiss();
            Argument.OnClose?.Invoke(isLevelUp, afterLevel);
        }
    }
}
