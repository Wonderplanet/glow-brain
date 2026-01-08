using WPFramework.Domain.Models;
using WPFramework.Modules.Environment;

namespace WPFramework.Domain.Translators
{
    public interface IEnvironmentTranslator
    {
        EnvironmentData TranslateToData(EnvironmentModel environmentModel);
        EnvironmentModel TranslateToModel(EnvironmentData environmentData);
    }
}
