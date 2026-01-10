using WPFramework.Domain.Models;
using WPFramework.Modules.Environment;

namespace WPFramework.Domain.Translators
{
    public interface IEnvironmentListTranslator
    {
        EnvironmentListData TranslateToData(EnvironmentListModel environmentListModel);
        EnvironmentListModel TranslateToModel(EnvironmentListData environmentListData);
    }
}
