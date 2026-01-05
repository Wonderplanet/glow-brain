using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Domain
{
    public class GetAppAppliedBalanceUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public AppAppliedBalanceUseCaseModel GetUseCaseModel()
        {
            return new AppAppliedBalanceUseCaseModel(GameRepository.GetGameFetch().UserParameterModel);
        }
    }
}
