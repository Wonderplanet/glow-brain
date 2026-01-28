using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public record HomeHeaderBadgeModel(NotificationBadge StaminaRecoverBadge, NotificationBadge UserAvatarBadge, NotificationBadge UserEmblemBadge);
    public sealed class HomeHeaderBadgeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUserProfileBadgeRepository UserProfileBadgeRepository { get; }
        [Inject] IUserEmblemBadgeRepository UserEmblemBadgeRepository { get; }
        [Inject] IUserStaminaModelFactory StaminaModelFactory { get; }
 
        public HomeHeaderBadgeModel GetHeaderBadgeModel()
        {
            var userStaminaModel = StaminaModelFactory.Create();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            return new HomeHeaderBadgeModel(
                userStaminaModel.CanRecoverByAd.ToNotificationBadge(),
                GetUserAvatarBadge(gameFetchOtherModel),
                GetUserEmblemBadge(gameFetchOtherModel));
        }

        NotificationBadge GetUserAvatarBadge(GameFetchOtherModel gameFetchOtherModel)
        {
            var displayAvatarList = UserProfileBadgeRepository.DisplayedUserProfileAvatarIds;
            var userUnitList = gameFetchOtherModel.UserUnitModels;

            foreach (var unit in userUnitList)
            {
                if (!displayAvatarList.Contains(unit.MstUnitId))
                {
                    return new NotificationBadge(true);
                }
            }

            return new NotificationBadge(false);
        }

        NotificationBadge GetUserEmblemBadge(GameFetchOtherModel gameFetchOtherModel)
        {
            var displayEmblemList = UserEmblemBadgeRepository.DisplayedUserEmblemIds;
            var userEmblemList = gameFetchOtherModel.UserEmblemModel;

            foreach (var emblem in userEmblemList)
            {
                if (!displayEmblemList.Contains(emblem.MstEmblemId))
                {
                    return new NotificationBadge(true);
                }
            }

            return new NotificationBadge(false);
        }
    }
}
