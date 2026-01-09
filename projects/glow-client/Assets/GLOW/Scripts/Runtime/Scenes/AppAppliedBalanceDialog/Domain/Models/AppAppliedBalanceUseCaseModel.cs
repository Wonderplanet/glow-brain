using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Domain
{
    public record AppAppliedBalanceUseCaseModel(UserParameterModel UserParameterModel)
    {
        public UserParameterModel UserParameterModel { get; } = UserParameterModel;
    }
}
