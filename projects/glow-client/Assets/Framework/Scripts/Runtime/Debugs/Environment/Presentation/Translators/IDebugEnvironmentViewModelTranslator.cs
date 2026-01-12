using WPFramework.Debugs.Environment.Presentation.ViewModels;
using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Presentation.Translators
{
    public interface IDebugEnvironmentViewModelTranslator
    {
        DebugEnvironmentSpecifiedDomainViewModel TranslateToViewModel(EnvironmentModel model);
        EnvironmentModel TranslateToModel(string name, string env, string description, DebugEnvironmentSpecifiedDomainViewModel viewModel);
    }
}
