using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Modules;

namespace GLOW.Core.Application.Configs
{
    public class ProductionAgreementHost : IAgreementHost
    {
        string IHost.Uri => "https://agrmt.channel.or.jp";
    }
}
