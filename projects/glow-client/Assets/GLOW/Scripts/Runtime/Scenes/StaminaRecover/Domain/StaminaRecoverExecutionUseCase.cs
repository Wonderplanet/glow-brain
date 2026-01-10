using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class StaminaRecoverExecutionUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IUserService UserService { get; }

        public async UniTask BuyStaminaFromDiamond(CancellationToken cancellationToken)
        {

            var model = await UserService.BuyStaminaDiamond(cancellationToken);
            UpdateGameFetchModel(model.UserParameterModel, model.UserBuyCountModel);

        }
        public async UniTask BuyStaminaFromAd(CancellationToken cancellationToken)
        {
            var model =  await UserService.BuyStaminaAd(cancellationToken);
            UpdateGameFetchModel(model.UserParameterModel, model.UserBuyCountModel);
        }

        void UpdateGameFetchModel(UserParameterModel parameterModel, UserBuyCountModel userBuyCountModel)
        {
            var defaultModel = GameRepository.GetGameFetch();
            var newFetch = defaultModel with
            {
                UserParameterModel = parameterModel,
                UserBuyCountModel = userBuyCountModel
            };

            GameManagement.SaveGameFetch(newFetch);
        }
    }
}
