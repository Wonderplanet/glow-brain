using WPFramework.Domain.Models;

namespace WPFramework.Domain.Modules
{
    public interface ISystemInfoProvider
    {
        ApplicationSystemInfo GetApplicationSystemInfo();
    }
}
