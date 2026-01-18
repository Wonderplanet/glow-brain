using System.Linq;
using WPFramework.Domain.Models;
using WPFramework.Modules.Environment;
using Zenject;

namespace WPFramework.Domain.Translators
{
    public class EnvironmentListTranslator : IEnvironmentListTranslator
    {
        [Inject] IEnvironmentTranslator EnvironmentTranslator { get; }

        EnvironmentListData IEnvironmentListTranslator.TranslateToData(EnvironmentListModel environmentListModel)
        {
            return new EnvironmentListData(
                Environments: environmentListModel.Environments.Select(EnvironmentTranslator.TranslateToData).ToArray()
            );
        }

        EnvironmentListModel IEnvironmentListTranslator.TranslateToModel(EnvironmentListData environmentListData)
        {
            return new EnvironmentListModel(
                Environments: environmentListData.Environments.Select(EnvironmentTranslator.TranslateToModel).ToArray()
            );
        }
    }
}
