using WPFramework.Domain.Modules;

namespace GLOW.Core.Application.Configs
{
    public class ProductionEnvironment : IEnvironment
    {
        string IEnvironment.EnvironmentName => "prd";
    }
}
