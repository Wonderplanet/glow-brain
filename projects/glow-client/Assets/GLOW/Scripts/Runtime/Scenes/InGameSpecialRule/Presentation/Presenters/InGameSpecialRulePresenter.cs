using GLOW.Scenes.InGameSpecialRule.Domain.UseCases;
using GLOW.Scenes.InGameSpecialRule.Presentation.Translators;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using Zenject;
namespace GLOW.Scenes.InGameSpecialRule.Presentation.Presenters
{
    public class InGameSpecialRulePresenter : IInGameSpecialRuleViewDelegate
    {
        [Inject] InGameSpecialRuleViewController ViewController { get; }
        [Inject] InGameSpecialRuleViewController.Argument Argument { get; }
        [Inject] InGameSpecialRuleUseCase InGameSpecialRuleUseCase { get; }

        public void OnViewDidLoad()
        {
            var model = InGameSpecialRuleUseCase.GetInGameSpecialRuleModel(Argument.SpecialRuleTargetMstId, Argument.SpecialRuleContentType);
            ViewController.SetViewModel(InGameSpecialRuleViewModelTranslator.TranslateInGameSpecialRuleViewModel(model, Argument.IsFromUnitSelect));
        }

        public void OnCloseSelected()
        {
            ViewController.Dismiss();
        }
    }
}
