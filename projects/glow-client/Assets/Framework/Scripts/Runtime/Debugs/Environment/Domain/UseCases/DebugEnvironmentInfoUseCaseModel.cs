using WPFramework.Debugs.Environment.Domain.Models;
using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.UseCases
{
    public record DebugEnvironmentInfoUseCaseModel(EnvironmentModel[] Environments, EnvironmentModel LastEnvironment, DebugEnvironmentTargetModel TargetEnvironment)
    {
        public EnvironmentModel[] Environments { get; } = Environments;
        public EnvironmentModel LastEnvironment { get; } = LastEnvironment;
        public DebugEnvironmentTargetModel TargetEnvironment { get; } = TargetEnvironment;
    }
}
