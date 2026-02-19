using WPFramework.Debugs.Environment.Domain.UseCases;
using WPFramework.Debugs.Environment.Presentation.Translators;
using WPFramework.Debugs.Environment.Presentation.ViewModels;
using WPFramework.Debugs.Environment.Presentation.Views;
using Zenject;

namespace WPFramework.Debugs.Environment.Presentation.Presenters
{
    public sealed class DebugEnvironmentSpecifiedDomainPresenter : IDebugEnvironmentSpecifiedDomainViewDelegate
    {
        [Inject] DebugEnvironmentSpecifiedDomainUseCases UseCases { get; }
        [Inject] DebugEnvironmentSpecifiedDomainViewController ViewController { get; }
        [Inject] IDebugEnvironmentViewModelTranslator ViewModelTranslator { get; }

        void IDebugEnvironmentSpecifiedDomainViewDelegate.OnViewDidLoad()
        {
            var environmentModel = UseCases.GetOrCreateSpecifiedEnvironment();
            var environmentViewModel = ViewModelTranslator.TranslateToViewModel(environmentModel);
            ViewController.SetViewModel(environmentViewModel);
        }

        void IDebugEnvironmentSpecifiedDomainViewDelegate.OnConfirm(DebugEnvironmentSpecifiedDomainViewModel model)
        {
            var environmentModel = UseCases.GetOrCreateSpecifiedEnvironment();
            var newEnvironmentModel = ViewModelTranslator.TranslateToModel(
                environmentModel.Name, environmentModel.Env, environmentModel.Description, model);
            UseCases.SaveSpecifiedEnvironment(newEnvironmentModel);
        }

        void IDebugEnvironmentSpecifiedDomainViewDelegate.OnReset()
        {
            UseCases.ResetSpecifiedEnvironment();
        }
    }
}
