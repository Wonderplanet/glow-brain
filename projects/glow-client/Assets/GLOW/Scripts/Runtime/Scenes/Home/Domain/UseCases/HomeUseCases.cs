using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public sealed class HomeUseCases : IHomeUseCases
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUserLevelDataRepository MstUserLevelDataRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }

        public HomeUserParameterUseCaseModel GetUserParameter()
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var level = UserLevelUpCacheRepository.GetPrevUserLevel().IsEmpty() ?
                userParameterModel.Level :
                UserLevelUpCacheRepository.GetPrevUserLevel();

            var mstUserLevelModel = MstUserLevelDataRepository.GetUserLevelModel(level);

            // NOTE: マスタデータから取得する予定
            var exp = UserLevelUpCacheRepository.GetPrevExp().IsEmpty() ?
                userParameterModel.Exp :
                UserLevelUpCacheRepository.GetPrevExp();
            var currentExp = mstUserLevelModel.ToRelativeUserExp(exp);
            var nextExp = mstUserLevelModel.RelativeNextLevelExp;

            // NOTE: スタミナの最大値をマスタから取得する
            var userStaminaModel = UserStaminaModelFactory.Create();

            return new HomeUserParameterUseCaseModel(
                level,
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

        public UserProfileModel GetUserProfile()
        {
            return GameRepository.GetGameFetchOther().UserProfileModel;

        }
    }
}
