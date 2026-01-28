using GLOW.Core.Constants;
using WPFramework.Domain.Modules;

namespace GLOW.Core.Application.Configs
{
    public sealed class EnvironmentHost : IEnvironmentHost
    {
        string IHost.Uri => Credentials.EnvironmentURL;
    }
}