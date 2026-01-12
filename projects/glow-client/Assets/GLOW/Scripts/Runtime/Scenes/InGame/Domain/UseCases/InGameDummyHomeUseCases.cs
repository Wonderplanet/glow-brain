using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    /// <summary>
    /// InGame用のHomeUseCasesダミー実装
    /// InGameシーンではHomeUseCasesの一部機能のみ使用するため、必要最小限の実装を提供
    /// </summary>
    public class InGameDummyHomeUseCases : IHomeUseCases
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUserLevelDataRepository MstUserLevelDataRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }

        HomeUserParameterUseCaseModel IHomeUseCases.GetUserParameter()
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var mstUserLevelModel = MstUserLevelDataRepository.GetUserLevelModel(userParameterModel.Level);

            var currentExp = mstUserLevelModel.ToRelativeUserExp(userParameterModel.Exp);
            var nextExp = mstUserLevelModel.RelativeNextLevelExp;

            var userStaminaModel = UserStaminaModelFactory.Create();

            return new HomeUserParameterUseCaseModel(
                userParameterModel.Level,
                currentExp,
                nextExp,
                userParameterModel.Coin,
                userStaminaModel.CurrentStamina,
                userStaminaModel.MaxStamina,
                userParameterModel.StaminaUpdatedAt,
                userParameterModel.FreeDiamond,
                userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId)
            );
        }

        UserProfileModel IHomeUseCases.GetUserProfile()
        {
            return GameRepository.GetGameFetchOther().UserProfileModel;
        }
    }
}

